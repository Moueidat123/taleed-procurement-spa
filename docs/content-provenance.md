# Source content, approval and visual provenance

## Assessment authority

The source of the procurement method is the user's supplied `Procurement_Self Assessment Tool_v01.xlsx`, attributed to **Aramco Taleed & Roland Berger**. The earlier audit recorded SHA-256:

`e86755a94db9bb4c2d14c3bf1f60eb237189d4a5bc1fd04efbb8d4faf197eecd`

For this prototype, the retrieved `procurement_framework_v1.json`, `Framework_Content_Catalogue.md`, `03_Workbook_Audit_and_Scoring.md`, and `04_Product_and_Statamic_Blueprint.md` supplied the wording, scoring contract and proposed journeys. `src/data/framework.json` is a **normalized reconstruction of that content**, not a byte-identical copy of the previously generated JSON and not a newly recalculated Excel workbook. Source cell references are carried with questions/actions.

The framework contains Category Management, Spend Analysis, Strategic Sourcing and Supplier Relationship Management, in that order; ten questions each; four bands and four actions per band/domain; four overall interpretations. Tests validate 40 distinct string IDs and 64 distinct action IDs. They do not establish that a human procurement subject-matter expert has signed off the transcription or digital method.

The previous source publication status was pending business approval. PM approval of approach/design does not automatically establish ownership clearance, wording approval or formal production publication. The app's seed publication reference explicitly says **DEMO-ONLY**. Managers can simulate a version's publication, not issue an actual authorized business sign-off.

Source references to local content, LCGPA, ICV, TCO and the original recommendation terminology remain intact. This application has no actual AI engine despite some source recommendations discussing AI-enabled procurement practices.

## Source locations

- Questions: worksheet `2. Self-Assessment`, column C, rows 8–17, 21–30, 34–43, 47–56.
- Recommendation groups: worksheet `4. Recommendations`, column C, rows 8–11, 15–18, 22–25, 29–32.
- Overall interpretations: original results-dashboard formula text as recorded in the previous extracted framework.

## Visual reference

The user's connected repository `FadiZahhar/taleedtoolsprototypes` was read, not modified. Reference file: `Taleed_Procurement_Assessment.html` on main, inspected commit `811ec359b8a486b47e9cd0a0f1b74f99b85a98ec`. The later shared Taleed design-token layer provides the grey background, white cards, navy, blue, amber, Inter/system stack, radii and focus treatment used here.

The functional SPA reimplements the experience using those foundations; this is **not a verified pixel-identical conversion of every original bundled screen**. No official font file is redistributed. The text wordmark/circle motif is presentation artwork, not a claim that official logo asset files were supplied. Obtain brand-owner approval for production assets.

## New proposed application behavior

Identity/profile fields, repeatable demonstration accounts, cycle settings, correction controls, consent copy and local storage recovery are application proposals/simulations, not content found in the original workbook. Detailed instructions and the interface label those distinctions. Synthetic companies do not represent actual Taleed participants or performance statistics.

Keep the assessment content and client-facing prototype in approved private workspaces unless the rights owner explicitly permits publication. No source framework copyright ownership is transferred by this package. Software dependencies retain their own licenses; generate/review third-party notices after the actual package installation.
