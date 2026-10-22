# Documents: policy pack, signatories and signed PDFs

Admin only (ability `documents.sign`; making a document public needs `content.manage`).

## The policy pack
17 policies (Code of Conduct, HR Policy Manual, Safeguarding, PSEA, ...) live as PDFs in `storage/private/policy-pack/` (upload the folder with the update zip).
**Documents > Import the policy pack** adds each one as a *private*, active document in the category Policies (version 1.0, authority Board of Directors).
A file already imported (same file name) is skipped, so the button is safe to press again. Nothing is public until you choose.

## Making a document public
Each row in Documents has a globe / lock button: one click makes that document public or private. Bulk make public / private still works too.
Public downloads (`/reports/download/{id}`) serve the **signed copy** when one exists, otherwise the original. A private document is always a 404.

## Signatories and signatures
1. **Documents > Signatories**: add each responsible person once (name, title, picture of the signature: PNG with transparent background is best, JPEG works, up to 1.5 MB). The picture is stored privately and never public by itself.
2. When adding a person, tick the documents they sign and choose the role on each one (Approved by, Endorsed by, Reviewed by, Prepared by, Witnessed by); "Select all policies" ticks the policies. Each person's card lists the documents they sign, the role, and whether it is signed. "Attach to more documents" adds others later; from one document, the row action **Signatures** also adds people.
3. **Apply signature** per person per document (or "Apply to all waiting documents" on the person). **Withdraw** takes it back.
4. Per document you can set version, approval authority and date of adoption: they print on the approval page.

## What the signed copy is
The original PDF is never modified. The signed copy is the original pages **plus one "Approval and signatures" page** (names, titles, signature images, date, document reference, and the SHA-256 fingerprint of the original), added as a PDF incremental update.
If a PDF cannot be extended safely, the signed copy is the approval page on its own (`signed_mode = page`). Rebuilding is automatic after any change; "Rebuild the signed copy" forces it.

## Database
Tables `document_signatories`, `document_signatures`; columns on `documents`: `version, approval_authority, adoption_date, signed_path, signed_size, signed_at, signed_mode` (additive, idempotent, in `database/schema.sql`). Until imported, the new screens show a notice and everything else works.

Test: `bin/selftest-document-signatures.php`.

## Reading who must sign (automatic)

The policies carry their own **Approval and signatures** page (the last page): one block per signer with the role printed in capitals (CHAIRPERSON, BOARD OF DIRECTORS), lines for Full name, Title / position and Date, and a Signature line. `PdfSlots` opens the PDF (classic cross-reference table, Flate / ASCII85 content streams), reads that page and returns each block: the printed role and the position of each line in PDF points. `DocumentSigning::detectSlots()` stores one row per block in `document_signatures` (`slot_index`, `role_label` = the printed role, `signatory_id` NULL = waiting for a person). It runs by itself after the policy pack import, on the first visit to Documents > Signatories, and with the buttons **Find who signs each document** / **Read the signature page**.

**The administrator only adds people.** Add a person with name, title and a signature picture. `autoAssign()` gives each waiting role to the active signatory whose title fits it best (same words after normalising: Chairperson / Chair, "of the Board" / "Board of Directors"; at least half the words, never overriding a person already chosen). Under **Who signs, read from the documents** every role found is listed with its number of documents: pick the person once for the whole role, then **Assign** or **Assign and sign**.

**Signing draws on the document's own lines**: the signature picture above the Signature line (between the Full name and Title captions), the person's name on the Full name line and the date on the Date line (the title line is already printed in the document). It is an incremental update (`PdfSigner::build()`): a Form XObject holding the drawing is added to the last page, the original bytes are not changed, `signed_mode = inplace`. Blocks that are not part of the document (people added by hand) still go on an appended "Approval and signatures" page (`merged`); if the PDF cannot be extended the signed copy is the approval page alone (`page`). Withdrawing a signature, replacing a picture (Rebuild) or deleting a person rebuilds the copy; a deleted person's blocks wait for someone else again.

Only the layout of the LAF policy pack is understood (one generator, one page layout). A PDF whose last page is not like that simply has no blocks: use "Add a person" on the document, which uses the appended approval page.
