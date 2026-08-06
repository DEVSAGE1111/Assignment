# Engineering Decisions & Architectural Choices

This document outlines the key engineering decisions, structural designs, and architectural trade-offs made during the development of Formify AI.

---

## 1. Database Architecture: JSON vs. Schema-per-Form
**Decision**: Store form schemas (field definitions) and submissions response data in JSON columns (`json` data type in MySQL).

### Rationale:
- **Flexibility**: Traditional database setups might create a new database table for each form. While this is highly structured, it requires running dynamic migrations in production, altering databases on-the-fly, and managing hundreds of tables, which does not scale well in multi-tenant environments.
- **Relational Integrity**: By using JSON columns, we keep our database schema fixed with just two main tables (`forms` and `submissions`).
- **Query Performance**: Modern MySQL natively supports indexing and querying JSON values, meaning search and retrieval operations are extremely fast.
- **Scalability**: Submissions are tied directly to form records through clean, indexed foreign keys, ensuring fast cascades and clean database garbage collection.

---

## 2. Zero-Dependency Excel & Word Document Parsing
**Decision**: Implement a custom, pure-PHP file parser (`DocumentParser`) using PHP's native `ZipArchive` and `SimpleXML` instead of installing third-party packages like `phpoffice/phpword` or `maatwebsite/excel`.

### Rationale:
- **No Dependency Bloat**: Heavy Office libraries frequently pull in dozens of sub-dependencies, expanding the `vendor/` folder, adding memory overhead, and risking dependency/composer version conflicts.
- **Office Formats are XML**: Both `.xlsx` and `.docx` files are simply compressed ZIP archives containing structured XML files (`xl/worksheets/sheet1.xml`, `word/document.xml`).
- **Speed & Control**: Extracting text directly from the underlying XML files using native PHP features operates at C-speed, is memory-safe, and provides full control over parsing without any extra baggage.
- **Safety**: Conforms to the codebase's strict guidelines of not changing application dependencies without explicit approval.

---

## 3. Database-Backed AI settings registry
**Decision**: Store all credential profiles (Gemini, OpenAI, xAI/Grok, Ollama) and active model configurations in a database table (`ai_settings` table) under a dynamic JSON column instead of standard `.env` configuration keys.

### Rationale:
- **Runtime Adaptability**: Users can edit keys, toggle provider accounts, and configure customized models on-the-fly directly inside the settings dashboard without rewriting host `.env` files or restarting production web-servers.
- **Advanced Connectivity Testers**: Built connection validators checking API tokens against provider endpoints before saving, preventing runtime errors.
- **Dynamic Model Lists**: Integrated provider lookup queries (such as retrieving active models list from Ollama or Google Language API) dynamically to populate dropdown lists.

---

## 4. Secure Route Authentication Wall
**Decision**: Implement a session-based login wall using a dedicated, high-fidelity Livewire login module (`/login`) and secure all admin routes using standard route middleware groups.

### Rationale:
- **Global Protection**: Wrapped dashboard, form generator interfaces, api document viewers, and configuration panels in the `auth` middleware, redirecting guests to the login screen automatically.
- **Exempt Public Viewers**: Excluded the dynamc form render route (`/forms/{uuid}`) from the auth group, allowing clients and guest visitors to fill out and submit responses securely without registration.

---

## 5. Modern Designer Light Slate Aesthetic
**Decision**: Design a clean, minimal light-mode developer theme (Slate-50 / Slate-900 contrast) inspired by modern products like Stripe and Vercel.

### Rationale:
- **Human-Crafted Feel**: Removed abstract purple gradient glows and floating AI indicators that look like generic templates. Instead, we introduce sharp 8px border-radius containers, solid dark buttons, bordered gray tags, and a low-opacity slate-200 dotted grid background.
- **Maximum Accessibility**: Monospaced tags, Outfit typography, and high-contrast tables improve readability and look like a premium corporate internal dashboard.
