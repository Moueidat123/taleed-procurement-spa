const {test}=require('node:test');
const assert=require('node:assert/strict');
const {SOURCE_FRAMEWORK:F,seedDatabase}=require('../.core/domain/seed.js');
const {classify,scoreAssessment,answersFromCounts,emptyAnswers,assertAnswers,completion}=require('../.core/domain/scoring.js');
const {applyCommand}=require('../.core/domain/commands.js');
const {validateDatabase,parseDatabase,assertFramework}=require('../.core/domain/validation.js');
const {canReadAssessment,canExport,effectiveSubmissions,isCycleOpen}=require('../.core/domain/policies.js');
const {LocalRepository,STORAGE_KEY,StorageConflictError}=require('../.core/infrastructure/localRepository.js');
const {toCsv,safeSpreadsheetText}=require('../.core/domain/csv.js');
let serial=0;
const context=(actorId='user-sahara',id=`test-${++serial}`,now='2026-09-15T10:00:00.000Z')=>({actorId,id,now});
const change=(db,command,actor='user-sahara',id)=>validateDatabase(applyCommand(db,command,context(actor,id)));
class MemoryStorage {
  constructor(){this.values=new Map();this.fail=false;}
  getItem(key){return this.values.get(key)??null;}
  setItem(key,value){if(this.fail)throw new Error('Quota exceeded');this.values.set(key,value);}
  removeItem(key){this.values.delete(key);}
}
test('source catalogue: 40 distinct string IDs, 64 recommendations and 4 interpretations',()=>{
  assertFramework(F);const ids=F.domains.flatMap(d=>d.questions.map(q=>q.id));
  assert.equal(ids.length,40);assert.equal(new Set(ids).size,40);assert(ids.includes('1.1'));assert(ids.includes('1.10'));
  assert.equal(F.domains.flatMap(d=>Object.values(d.recommendations).flat()).length,64);
  assert.equal(Object.keys(F.interpretations).length,4);
});
test('seed is valid, deterministic, synthetic and contains a 12-answer draft',()=>{
  const first=seedDatabase();const second=seedDatabase();assert.deepEqual(first,second);
  assert.equal(Object.keys(first.organizations).length,5);assert.equal(Object.keys(first.assessments).length,6);
  assert.equal(completion(first.assessments['assessment-sahara-2026'].answers),12);
});
test('all No and all Yes produce exactly 0% and 100%',()=>{
  assert.equal(scoreAssessment(answersFromCounts(F,[0,0,0,0]),F).overall,0);
  const full=scoreAssessment(answersFromCounts(F,[10,10,10,10]),F);assert.equal(full.overall,100);assert.equal(full.band,'best_in_class');
  assert.equal(new Set(full.priorities).size,3);assert.equal(full.domains.flatMap(d=>d.actions).length,16);
});
test('unrounded maturity boundaries are inclusive on the upper bound',()=>{
  for(const [value,band] of [[0,'foundational'],[40,'foundational'],[40.01,'developing'],[65,'developing'],[65.01,'advanced'],[80,'advanced'],[80.01,'best_in_class'],[100,'best_in_class']])assert.equal(classify(value),band);
  for(const value of [-1,101,NaN,Infinity])assert.throws(()=>classify(value));
});
test('40-question total boundaries: 16/17, 26/27 and 32/33 Yes answers',()=>{
  for(const [total,band] of [[16,'foundational'],[17,'developing'],[26,'developing'],[27,'advanced'],[32,'advanced'],[33,'best_in_class']]){
    let left=total;const counts=Array.from({length:4},()=>{const count=Math.min(left,10);left-=count;return count;});
    const result=scoreAssessment(answersFromCounts(F,counts),F);assert.equal(result.overall,total*2.5);assert.equal(result.band,band);
  }
});
test('known 6/4/8/6 fixture: 60% Developing; Spend, Category, SRM priorities',()=>{
  const r=scoreAssessment(answersFromCounts(F,[6,4,8,6]),F);
  assert.equal(r.overall,60);assert.equal(r.band,'developing');
  assert.deepEqual(r.priorities,['spend_analysis','category_management','supplier_relationship_management']);assert.equal(r.hasTie,true);
  assert.equal(r.domains[1].actions[0].id,'spend_analysis.foundational.1');
  assert.equal(r.domains[2].actions[0].id,'strategic_sourcing.advanced.1');
});
test('all 14,641 domain-count combinations preserve score, exact band, unique stable priorities and correct recommendation groups',()=>{
  let count=0;const keys=F.domains.map(d=>d.key);
  const expectedBand=v=>v<=40?'foundational':v<=65?'developing':v<=80?'advanced':'best_in_class';
  for(let a=0;a<=10;a++)for(let b=0;b<=10;b++)for(let c=0;c<=10;c++)for(let d=0;d<=10;d++){
    const counts=[a,b,c,d];const r=scoreAssessment(answersFromCounts(F,counts),F);const total=a+b+c+d;
    assert.equal(r.yes,total);assert.equal(r.overall,total*2.5);assert.equal(r.band,expectedBand(total*2.5));
    assert.deepEqual(r.priorities,counts.map((value,i)=>({value,i})).sort((x,y)=>x.value-y.value||x.i-y.i).slice(0,3).map(x=>keys[x.i]));
    r.domains.forEach((domain,i)=>{assert.equal(domain.score,counts[i]*10);assert.equal(domain.band,expectedBand(counts[i]*10));assert.equal(domain.actions.length,4);assert.equal(domain.actions[0].id,`${keys[i]}.${expectedBand(counts[i]*10)}.1`);});count++;
  }assert.equal(count,14641);
});
test('unanswered is not No; incomplete submissions cannot be scored',()=>{
  const answers=emptyAnswers(F);assert.equal(completion(answers),0);answers['1.1']='no';assert.equal(completion(answers),1);
  assert.throws(()=>scoreAssessment(answers,F),/Answer question/);answers['1.1']=null;assert.equal(completion(answers),0);
});
test('unknown/missing IDs, invalid values and duplicate framework IDs are rejected',()=>{
  const valid=answersFromCounts(F,[1,2,3,4]);assert.throws(()=>assertAnswers({...valid,'9.9':'yes'},F));
  const missing={...valid};delete missing['1.10'];assert.throws(()=>assertAnswers(missing,F));
  assert.throws(()=>assertAnswers({...valid,'1.1':'N/A'},F));assert.throws(()=>assertAnswers({...valid,'1.1':1},F));
  const bad=structuredClone(F);bad.domains[0].questions[9].id='1.1';assert.throws(()=>assertFramework(bad));
});
test('partial save and clear update only the draft, with no final snapshot',()=>{
  const original=seedDatabase();const before=JSON.stringify(original);const id='assessment-sahara-2026';
  const saved=change(original,{type:'answer',assessmentId:id,questionId:'2.3',value:'no'});
  assert.equal(completion(saved.assessments[id].answers),13);assert.equal(saved.assessments[id].snapshot,null);assert.equal(JSON.stringify(original),before);
  const cleared=change(saved,{type:'answer',assessmentId:id,questionId:'2.3',value:null});assert.equal(completion(cleared.assessments[id].answers),12);
});
test('submission requires all answers and declaration, then snapshots immediately',()=>{
  const id='assessment-sahara-2026';const initial=seedDatabase();assert.throws(()=>applyCommand(initial,{type:'submit',assessmentId:id,declaration:true},context()));
  initial.assessments[id].answers=answersFromCounts(F,[6,4,8,6]);
  assert.throws(()=>applyCommand(initial,{type:'submit',assessmentId:id,declaration:false},context()));
  const submitted=change(initial,{type:'submit',assessmentId:id,declaration:true});assert.equal(submitted.assessments[id].status,'submitted');assert.equal(submitted.assessments[id].snapshot.result.overall,60);
  assert.equal(applyCommand(submitted,{type:'submit',assessmentId:id,declaration:true},context()),submitted);
});
test('submitted answers cannot be edited; old profile snapshots do not change',()=>{
  const db=seedDatabase();const a=db.assessments['assessment-namaa-2026'];const snapshot=JSON.stringify(a.snapshot);
  assert.throws(()=>applyCommand(db,{type:'answer',assessmentId:a.id,questionId:'1.1',value:'no'},context('user-namaa')),/cannot be edited/);
  const {id,active,...profile}=db.organizations['org-namaa'];const changed=change(db,{type:'saveProfile',profile:{...profile,name:'Namaa Updated Demo'}},'user-namaa');
  assert.equal(JSON.stringify(changed.assessments[a.id].snapshot),snapshot);assert.equal(changed.organizations[id].active,active);
});
test('correction remains a draft while original stays effective; resubmission advances effective revision',()=>{
  const db=seedDatabase();const original='assessment-namaa-2026';const old=JSON.stringify(db.assessments[original]);
  const corrected=change(db,{type:'openCorrection',assessmentId:original,reason:'Correcting a misunderstood source question.'},'user-manager','correction-1');
  assert.equal(corrected.assessments['correction-1'].revision,2);assert.equal(effectiveSubmissions(corrected,'2026').find(a=>a.orgId==='org-namaa').id,original);
  assert.equal(JSON.stringify(corrected.assessments[original]),old);
  const edited=change(corrected,{type:'answer',assessmentId:'correction-1',questionId:'1.1',value:'no'},'user-namaa');
  const submitted=change(edited,{type:'submit',assessmentId:'correction-1',declaration:true},'user-namaa');
  assert.equal(effectiveSubmissions(submitted,'2026').find(a=>a.orgId==='org-namaa').id,'correction-1');assert.equal(JSON.stringify(submitted.assessments[original]),old);
  assert.throws(()=>applyCommand(submitted,{type:'openCorrection',assessmentId:original,reason:'Outdated revision should be rejected.'},context('user-manager')));
});
test('duplicate drafts/correction requests are blocked',()=>{
  const db=seedDatabase();assert.throws(()=>applyCommand(db,{type:'startAssessment',cycleId:'2026'},context()));
  const corrected=change(db,{type:'openCorrection',assessmentId:'assessment-namaa-2026',reason:'Valid correction explanation.'},'user-manager');
  assert.throws(()=>applyCommand(corrected,{type:'openCorrection',assessmentId:'assessment-namaa-2026',reason:'Second concurrent correction.'},context('user-manager')));
});
test('company scoping blocks another company; staff cannot edit or see draft answers in read policy',()=>{
  const db=seedDatabase();const draft=db.assessments['assessment-sahara-2026'];const submitted=db.assessments['assessment-namaa-2026'];
  assert.equal(canReadAssessment(db.users['user-sahara'],submitted),false);assert.equal(canReadAssessment(db.users['user-analyst'],draft),false);
  assert.equal(canReadAssessment(db.users['user-analyst'],submitted),true);
  assert.throws(()=>applyCommand(db,{type:'answer',assessmentId:draft.id,questionId:'1.1',value:'yes'},context('user-namaa')));
  assert.throws(()=>applyCommand(db,{type:'answer',assessmentId:draft.id,questionId:'1.1',value:'yes'},context('user-admin')));
});
test('closed cycles and paused organizations block editing and submission',()=>{
  const db=seedDatabase();const closed=change(db,{type:'saveCycle',cycle:{...db.cycles['2026'],status:'closed'}},'user-manager');
  assert.throws(()=>applyCommand(closed,{type:'answer',assessmentId:'assessment-sahara-2026',questionId:'1.1',value:'yes'},context()));
  const paused=change(db,{type:'setOrganizationActive',orgId:'org-sahara',active:false},'user-manager');
  assert.throws(()=>applyCommand(paused,{type:'submit',assessmentId:'assessment-sahara-2026',declaration:true},context()));
  assert.equal(isCycleOpen(db.cycles['2026'],'2027-01-01T00:00:00.000Z'),false);
  assert.equal(isCycleOpen(db.cycles['2026'],'2026-12-31T23:59:59.999Z'),true);
});
test('registration normalizes email, requires consent, never accepts role escalation or password storage',()=>{
  const db=seedDatabase();const registered=change(db,{type:'register',name:'Demo Person',email:' NEW@EXAMPLE.COM ',jobTitle:'Procurement lead',consent:true,role:'admin',password:'not-stored'},null,'new-user');
  assert.equal(registered.users['new-user'].role,'champion');assert.equal(registered.users['new-user'].email,'new@example.com');assert.equal('password'in registered.users['new-user'],false);
  assert.throws(()=>applyCommand(registered,{type:'register',name:'Duplicate',email:'new@example.com',jobTitle:'CEO',consent:true},context(null)));
  assert.throws(()=>applyCommand(db,{type:'register',name:'Demo',email:'valid@example.com',jobTitle:'CEO',consent:false},context(null)));
});
test('unverified account cannot start; verification and complete profile enable a new cycle draft',()=>{
  const db=change(seedDatabase(),{type:'register',name:'New Champion',email:'new@example.com',jobTitle:'CEO',consent:true},null,'new-user');
  assert.throws(()=>applyCommand(db,{type:'startAssessment',cycleId:'2026'},context('new-user')));
  assert.throws(()=>applyCommand(db,{type:'verify',code:'111111'},context('new-user')));
  const verified=change(db,{type:'verify',code:'123456'},'new-user');
  const profile={name:'Brand New Demonstration',country:'Saudi Arabia',sector:'Technology',size:'1–10',registrationId:'NEW-DEMO',authorityConfirmed:true};
  const company=change(verified,{type:'saveProfile',profile},'new-user','new-org');
  const started=change(company,{type:'startAssessment',cycleId:'2026'},'new-user','new-draft');
  assert.equal(completion(started.assessments['new-draft'].answers),0);assert.equal(started.assessments['new-draft'].orgId,'new-org');
});
test('duplicate company names and registration references are rejected',()=>{
  const db=seedDatabase();const {id,active,...profile}=db.organizations['org-sahara'];
  assert.throws(()=>applyCommand(db,{type:'saveProfile',profile:{...profile,name:'Namaa Logistics'}},context()));
  assert.throws(()=>applyCommand(db,{type:'saveProfile',profile:{...profile,registrationId:'DEMO-NAMAA'}},context()));
  assert.equal(id,'org-sahara');assert(active);
});
test('framework clone/publication preserves content and historical snapshots',()=>{
  const db=seedDatabase();const old=JSON.stringify(db.assessments['assessment-namaa-2026'].snapshot);
  const cloned=change(db,{type:'cloneFramework',sourceVersion:'1.0.0',version:'1.1.0'},'user-manager');assert.equal(cloned.frameworks['1.1.0'].status,'draft');
  assert.throws(()=>applyCommand(cloned,{type:'publishFramework',version:'1.1.0',approvalReference:'short'},context('user-manager')));
  const published=change(cloned,{type:'publishFramework',version:'1.1.0',approvalReference:'DEMO-APPROVAL-002'},'user-manager');
  assert.equal(published.frameworks['1.1.0'].status,'published');assert.equal(JSON.stringify(published.assessments['assessment-namaa-2026'].snapshot),old);
  assert.throws(()=>applyCommand(published,{type:'saveCycle',cycle:{...published.cycles['2026'],frameworkVersion:'1.1.0'}},context('user-manager')));
});
test('analyst export grants are explicit; only management can grant them',()=>{
  const db=seedDatabase();assert.equal(canExport(db.users['user-analyst']),false);assert.equal(canExport(db.users['user-manager']),true);
  assert.throws(()=>applyCommand(db,{type:'recordExport',format:'csv',count:4},context('user-analyst')));
  const updated=change(db,{type:'setUserAccess',userId:'user-analyst',active:true,canExport:true},'user-manager');assert.equal(canExport(updated.users['user-analyst']),true);
  const logged=change(updated,{type:'recordExport',format:'csv',count:4},'user-analyst');assert.equal(logged.audit[0].action,'recordExport');
  assert.throws(()=>applyCommand(db,{type:'setUserAccess',userId:'user-analyst',active:true,canExport:true},context()));
});
test('administrator/self-access protection is enforced',()=>{
  const db=seedDatabase();assert.throws(()=>applyCommand(db,{type:'setUserAccess',userId:'user-admin',active:false,canExport:false},context('user-admin')));
  assert.throws(()=>applyCommand(db,{type:'setUserAccess',userId:'user-admin',active:false,canExport:false},context('user-manager')));
  assert.throws(()=>applyCommand(db,{type:'createStaff',name:'Bad admin',email:'bad@example.com',role:'admin'},context('user-manager')));
});
test('persistence hydrates unchanged data, checks revisions and never silently resets corruption',()=>{
  const memory=new MemoryStorage();const repo=new LocalRepository(()=>memory);const db=repo.initialize(seedDatabase('a'));
  assert.equal(repo.initialize(seedDatabase('b')).instanceId,'a');assert.deepEqual(repo.load(),db);
  const next=applyCommand(db,{type:'answer',assessmentId:'assessment-sahara-2026',questionId:'2.3',value:'no'},context());
  repo.commit(db,next);assert.equal(repo.load().revision,1);assert.throws(()=>repo.commit(db,next),StorageConflictError);
  memory.setItem(STORAGE_KEY,'{corrupted');assert.throws(()=>repo.initialize(seedDatabase()));assert.equal(memory.getItem(STORAGE_KEY),'{corrupted');
});
test('quota failures do not claim success or overwrite the saved revision',()=>{
  const memory=new MemoryStorage();const repo=new LocalRepository(()=>memory);const db=repo.initialize(seedDatabase());const original=repo.raw();memory.fail=true;
  const next=applyCommand(db,{type:'answer',assessmentId:'assessment-sahara-2026',questionId:'2.3',value:'no'},context());
  assert.throws(()=>repo.commit(db,next),/Nothing was saved/);assert.equal(repo.raw(),original);
});
test('corrupt/unknown-schema/tampered/unsafe backups are rejected before restoration',()=>{
  const db=seedDatabase();assert.deepEqual(parseDatabase(JSON.stringify(db)),db);
  assert.throws(()=>parseDatabase('{oops'));assert.throws(()=>parseDatabase(JSON.stringify({...db,schemaVersion:2})));
  const modified=structuredClone(db);modified.assessments['assessment-namaa-2026'].snapshot.result.overall=100;assert.throws(()=>validateDatabase(modified));
  assert.throws(()=>parseDatabase('{"__proto__":{"polluted":true}}'));assert.equal({}.polluted,undefined);
  assert.throws(()=>parseDatabase(' '.repeat(3500001)));
});
test('CSV formulas, quotes, newlines and non-ASCII values are handled safely',()=>{
  for(const value of ['=1+1','+SUM(A1:A2)','-2+1','@cmd','\t=HYPERLINK("x")'])assert.equal(safeSpreadsheetText(value),`'${value}`);
  const result=toCsv([['Name','Value'],['شركة, Demo','a"b\nc'],['=danger',42]]);
  assert(result.startsWith('\uFEFF'));assert(result.includes('"a""b\nc"'));assert(result.includes('"\'=danger"'));assert(result.includes('"شركة, Demo"'));
});
test('audit caps at 500 events and never includes answer values in answer details',()=>{
  let db=seedDatabase();for(let i=0;i<510;i++)db=applyCommand(db,{type:'answer',assessmentId:'assessment-sahara-2026',questionId:'2.3',value:i%2?'yes':'no'},context());
  assert.equal(db.audit.length,500);assert(db.audit[0].detail.includes('answer content omitted'));validateDatabase(db);
});
test('draft content editing is validated and cannot mutate published content or source IDs',()=>{
  const db=seedDatabase();assert.throws(()=>applyCommand(db,{type:'updateDraftContent',version:'1.0.0',contentType:'question',targetId:'1.1',text:'An attempt to alter published content.'},context('user-manager')));
  const cloned=change(db,{type:'cloneFramework',sourceVersion:'1.0.0',version:'1.1.0'},'user-manager');
  assert.throws(()=>applyCommand(cloned,{type:'updateDraftContent',version:'1.1.0',contentType:'question',targetId:'1.1',text:'short'},context('user-manager')));
  const edited=change(cloned,{type:'updateDraftContent',version:'1.1.0',contentType:'question',targetId:'1.1',text:'Demonstration replacement question requiring formal content review?'},'user-manager');
  assert.equal(edited.frameworks['1.0.0'].content.domains[0].questions[0].text,F.domains[0].questions[0].text);
  assert.equal(edited.frameworks['1.1.0'].content.domains[0].questions[0].id,'1.1');
  assert(edited.frameworks['1.1.0'].content.domains[0].questions[0].text.startsWith('Demonstration replacement'));
  const published=change(edited,{type:'publishFramework',version:'1.1.0',approvalReference:'DEMO-CONTENT-APPROVAL'},'user-manager');
  assert.throws(()=>applyCommand(published,{type:'updateDraftContent',version:'1.1.0',contentType:'question',targetId:'1.1',text:'Another attempt after publication.'},context('user-manager')));
});
