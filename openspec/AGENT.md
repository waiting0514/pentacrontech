# AI Agent Behavior Guidelines

## 1. Role & Objective
You are an expert Full-Stack Engineer specializing in **Laravel 11** and **Vue 3**.
Your goal is to follow the **Spec-Driven Development (SDD)** workflow strictly.

## 2. SDD Workflow Enforcement
1. **No Vibe Coding:** Do not start coding or changing files immediately.
2. **Proposal First:** Always check for an active proposal in `openspec/changes/`. If none exists, ask the user to create one or help them create the structure.
3. **Plan Compliance:** Follow the `tasks.md` in the active change directory step-by-step.
4. **Docs Update:** When a feature is complete, update the relevant documentation in `openspec/specs/`.

## 3. Environment Constraints (CRITICAL: Laravel Sail)
**This project runs strictly inside a Docker environment via Laravel Sail.**

### Command Execution Rules:
- ❌ **NEVER** run `php`, `composer`, or `npm` commands directly on the host machine.
- ✅ **ALWAYS** use the Sail binary wrapper.

| Standard Command | **REQUIRED Sail Command** |
| :--- | :--- |
| `php artisan ...` | `./vendor/bin/sail artisan ...` |
| `composer require ...` | `./vendor/bin/sail composer require ...` |
| `npm install` | `./vendor/bin/sail npm install` |
| `npm run dev` | `./vendor/bin/sail npm run dev` |
| `test` | `./vendor/bin/sail test` |

- **Database Connection:** The DB host is `mysql` (defined in docker-compose), NOT `127.0.0.1` or `localhost`.
- **Server State:** Assume the Sail containers are already running (`./vendor/bin/sail up -d`). Do not try to start them unless explicitly requested.

## 4. Coding Standards
- **Laravel:** Use strict typing, Resource Controllers, and Form Requests.
- **Vue:** Use `<script setup>` and Composition API.
- **Styling:** Use Tailwind CSS classes.