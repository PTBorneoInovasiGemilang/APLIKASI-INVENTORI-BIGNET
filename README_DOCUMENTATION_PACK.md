# BIGNET DOCUMENTATION PACK

Copy the `docs/` folder into the project root.

Recommended project structure:

APLIKASI INVENTORI BIGNET/
- AGENTS.md
- MASTER_BIGNET_PROJECT_V2.md
- BIGNET_ANTIGRAVITY_PROJECT_MEMORY.md
- MASTER_PROGRAM_PRD_INDUK_BIGNET.md
- docs/
  - PROJECT_STATE.md
  - BUSINESS_FLOW.md
  - MENU_ROLE_MATRIX.md
  - DATABASE_BLUEPRINT.md
  - DECISIONS.md
  - OPEN_DECISIONS.md
  - CHANGELOG.md
  - BUG_TRACKER.md
  - UAT_STATUS.md
  - AGENTS_DOCUMENTATION_PROTOCOL.md

## Operating rule

Before work:
MASTER → STATE → DECISIONS → OPEN_DECISIONS → relevant trackers.

After work:
STATE → CHANGELOG → BUG/UAT/DECISIONS as needed.

The goal is that a new AI session can reconstruct project context from repository documentation without relying on previous chat history.
