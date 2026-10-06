## Context
The application needs to support an "Inquiry Mode" for products, where price is hidden and users are encouraged to contact the seller. This requires changes across the database, API, and frontend. We are in a development phase where we can modify existing migrations directly.

## Goals / Non-Goals

**Goals:**
- Enable per-product "Inquiry Only" configuration.
- Enable project-level default setting for "Inquiry Only".
- Provide batch operations for managing this state.
- Update frontend to reflect these states in forms and lists.

**Non-Goals:**
- Complex pricing rules or tiered access (simple boolean toggle only).
- modifying existing migration history (we will modify files in place).

## Decisions

### Database Schema Modification
- **Decision**: Add columns directly to the `create_products_table` and `create_projects_table` (or equivalent) migration files.
- **Rationale**: User instruction for the current development phase to avoid migration clutter.

### Batch Action Implementation
- **Decision**: Implement a dedicated `batch-action` endpoint rather than multiple single-resource calls.
- **Rationale**: Performance and atomicity for bulk operations.

### Frontend State Management
- **Decision**: Use the existing state management (Vue refs/stores) to toggle UI elements based on the new flags.
- **Rationale**: Consistent with current Vue architecture.

## Risks / Trade-offs

- **Risk**: Modifying existing migrations requires a database refresh (`migrate:fresh`).
- **Mitigation**: Acceptable in this dev phase; will ensure seeders are updated if necessary (though not explicitly requested, implied by fresh migration).

- **Risk**: Batch actions might timeout with very large selections.
- **Mitigation**: Not a concern for current prototype scale.
