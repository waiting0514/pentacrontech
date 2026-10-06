# Project Constitution

## 1. Technology Stack
- **Framework:** Laravel 11
- **Frontend:** Vue 3 (Composition API) + Tailwind CSS
- **Database:** MySQL 8.0
- **AI Integration:** OpenAI PHP SDK, Photoroom SDK, Clipdrop SDK

## 2. System Architecture
### Core Philosophy
- **Monolithic Architecture**: Single application managing multiple websites ("Projects").
- **Admin-Centric**: Single administrative user managing all projects. No public registration.
- **Project-Based Budgeting**: Budget and usage tracking are bound to 'Projects', not users.

### Data Model
- **User**: Administrator (Role: admin).
- **Project**: Represents a client website. Has `monthly_budget_usd` and `monthly_usage_usd`.
- **Product**: Content managed within a project.
- **AiUsageLog**: Records AI costs, linked to a specific Project.

## 3. Development Environment & Tooling (Crucial)
- **Environment:** Docker via **Laravel Sail**
- **Command Execution Rule:** ALL commands must be executed through the Sail binary.
  - ❌ DO NOT run: `php artisan ...` or `composer ...` or `npm ...`
  - ✅ MUST run: `./vendor/bin/sail artisan ...`
  - ✅ MUST run: `./vendor/bin/sail composer ...`
  - ✅ MUST run: `./vendor/bin/sail npm ...`
- **Database Host:** `mysql` (Docker service name)

## 4. Key Configurations
- **AI Pricing**: `config/ai_prices.php` defines cost per token/operation.
- **API Keys**: Managed locally in `.env` (OPENAI_API_KEY, etc.).
- **Scheduler**: Monthly budget reset via `php artisan usage:reset-monthly`.

## 5. Security Protocols
- **Authentication**: Laravel Sanctum (Stateful).
- **Access Control**: Strict `auth:sanctum` middleware on all API routes.
- **Registration**: Public registration routes are DISABLED.

## 6. Frontend Guidelines
- **Single Site**: Each container serves one website. No project switching needed.
- **API Calls**: AI/Product APIs use the single project context from the backend.
- **Components**:
  - `SystemSettings`: Configure site name, domain, and monthly budget.
  - `UsageMonitor`: Displays budget status and usage logs.