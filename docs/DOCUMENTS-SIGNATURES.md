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
2. Attach the person to documents (all policies, or a chosen list) with a role (Approved by, Endorsed by, Reviewed by, Prepared by, Witnessed by), or from one document: row action **Signatures**.
3. **Apply signature** per person per document (or "Apply to all waiting documents" on the person). **Withdraw** takes it back.
4. Per document you can set version, approval authority and date of adoption: they print on the approval page.

## What the signed copy is
The original PDF is never modified. The signed copy is the original pages **plus one "Approval and signatures" page** (names, titles, signature images, date, document reference, and the SHA-256 fingerprint of the original), added as a PDF incremental update.
If a PDF cannot be extended safely, the signed copy is the approval page on its own (`signed_mode = page`). Rebuilding is automatic after any change; "Rebuild the signed copy" forces it.

## Database
Tables `document_signatories`, `document_signatures`; columns on `documents`: `version, approval_authority, adoption_date, signed_path, signed_size, signed_at, signed_mode` (additive, idempotent, in `database/schema.sql`). Until imported, the new screens show a notice and everything else works.

Test: `bin/selftest-document-signatures.php`.
