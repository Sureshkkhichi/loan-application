# Project Change Management & Traceability System

This directory serves as the **Permanent Project Change Log** and **Single Source of Truth for Project History and Evolution**, operating alongside `/requirements`.

## 📁 Directory Structure & Purpose

```text
project-logs/
├── README.md               # Directory governance, indexing, and standard operating procedures (SOP)
├── discussions/            # Architectural debates, analysis reports, BEFORE/AFTER proposals
├── decisions/              # Formal Architecture Decision Records (ADRs) and technical choices
├── approvals/              # Explicit user approvals, sign-offs, and requirement baselines
├── revisions/              # Major scope, requirement, or design revisions over project phases
└── changes/                # Detailed logs of executed changes, affected components, and test evidence
```

---

## 📜 Governance Rules & Operating Standard

1. **Before/After Analysis Mandatory**: Every proposed modification must be preceded by a thorough BEFORE vs AFTER technical impact analysis.
2. **Immediate Issue Reporting**: Any conflict, technical limitation, regression risk, or security gap is reported immediately with recommended solutions before proceeding.
3. **Approval for Material Changes**: No change to business logic, APIs, schema, architecture, or existing flows is executed without explicit user sign-off.
4. **Append-Only History**: Records must never be overwritten or deleted. Revisions reference prior decisions to maintain complete genealogical traceability.
5. **Pre-Task History Review**: Project logs are consulted before undertaking any task to prevent regression or re-introducing rejected approaches.
6. **Cross-Check with Requirements**: Changes must synchronize with `requirements/requirements_english.md` and `requirements/requirements_hinglish.md`.
