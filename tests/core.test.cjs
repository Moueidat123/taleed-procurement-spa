const {test}=require('node:test');
const assert=require('node:assert/strict');
const {SOURCE_FRAMEWORK:F,assertFramework}=require('../.core/domain/framework.js');
const {classify,scoreAssessment,answersFromCounts,emptyAnswers,assertAnswers,completion}=require('../.core/domain/scoring.js');
const {toCsv,safeSpreadsheetText}=require('../.core/domain/csv.js');
test('source catalogue: 40 distinct string IDs, 64 recommendations and 4 interpretations',()=>{
  assertFramework(F);const ids=F.domains.flatMap(d=>d.questions.map(q=>q.id));
  assert.equal(ids.length,40);assert.equal(new Set(ids).size,40);assert(ids.includes('1.1'));assert(ids.includes('1.10'));
  assert.equal(F.domains.flatMap(d=>Object.values(d.recommendations).flat()).length,64);
  assert.equal(Object.keys(F.interpretations).length,4);
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
test('CSV formulas, quotes, newlines and non-ASCII values are handled safely',()=>{
  for(const value of ['=1+1','+SUM(A1:A2)','-2+1','@cmd','\t=HYPERLINK("x")'])assert.equal(safeSpreadsheetText(value),`'${value}`);
  const result=toCsv([['Name','Value'],['شركة, Demo','a"b\nc'],['=danger',42]]);
  assert(result.startsWith('\uFEFF'));assert(result.includes('"a""b\nc"'));assert(result.includes('"\'=danger"'));assert(result.includes('"شركة, Demo"'));
});
