# AI Log

How AI assistants were used in this project, what they got right, and where they were wrong.
New entries are added with the `update-ai-log` skill (`.github/skills/update-ai-log/SKILL.md`).

## Milestone 1

### Editor assistant setup
- **Assistant:** GitHub Copilot Chat
- **Editor:** Visual Studio Code
- **Setup note:** The first message failed with "Language model unavailable" because I had not signed in to GitHub in VS Code. After signing in with GitHub, Copilot worked.

### 2026-10-15: Ask mode: does Copilot follow AGENTS.md?
- **Tool / mode:** GitHub Copilot Chat, Ask mode
- **Task:** Check that the assistant reads and uses the project rules.
- **Prompt:** "What are the SPA rules in this project?"
- **Result:** Copilot listed all 8 frontend rules: the shell only in `index.html`, views as fragments registered in `VIEWS`,
  hash routes and never `href="#"`, no `<script>` in views, event delegation with `closest()`, theme variables only,
  375 px without horizontal scroll, `data.js` as the data source, and keeping the `data-external-api="currency-converter"` marker.
  It ended with "This is all from AGENTS.md."
- **Correct?** Yes. Every point matches `AGENTS.md`.
- **What I learned:** Copilot picks up `AGENTS.md` automatically, so the rules I write there shape its answers.

### 2026-10-15: Completions: AI was wrong (broken product entry)
- **Tool / mode:** GitHub Copilot inline completions
- **Task:** Add a 9th product to `PRODUCTS` in `frontend/js/data.js`.
- **Prompt:** Started typing `{ id: 9, name: "` after the last product and accepted the suggestion with Tab.
- **Result:** Copilot suggested a "Wool Fedora Hat" with price, colours and sizes in the right shape, but:
  1. The suggestion did not add a comma after the previous product (`}` instead of `},`) and dropped the opening `{`,
     so `data.js` had a syntax error (`SyntaxError: Unexpected identifier 'id'`). Because the whole file failed,
     no products rendered anywhere on the site.
  2. It used `icon: "bi-hat"`, which does not exist in Bootstrap Icons 1.11.3, so the product tile would show no icon.
- **Correct?** Partly. I fixed the comma and the brace by hand, checked the file with `node --check frontend/js/data.js`,
  and replaced `bi-hat` with `bi-gem`, an icon that exists.
- **What I learned:** Completions only see the text around the cursor, so they can break the surrounding syntax and invent names that look real.
  I have to read what I accept and check the page afterwards.

### 2026-10-15: Agent mode: make the shop "Sort" dropdown work
- **Tool / mode:** GitHub Copilot Chat, Agent mode (Frontend agent)
- **Task:** The Sort dropdown on `#shop` did nothing.
- **Prompt:** "Make the Sort dropdown on the shop page actually sort the products."
- **Result:** The agent changed 2 files:
  - `frontend/views/shop.html`: gave the select an `id="shopSort"` and `value` attributes on each option.
  - `frontend/js/app.js`: added a `SHOP_STATE` object (current category and sort); `renderShop()` now copies the
    list, sorts by price ascending or descending, and keeps the select in sync; category filters keep the
    current sort; a delegated `change` listener on `document` handles `#shopSort`, following the AGENTS.md
    rule on event delegation.
- **Diff review:** I reviewed both files in the VS Code diff view before keeping them. I checked that it did not fetch views again,
  did not add a library, and used event delegation. I kept all the changes. I tested `#shop`: both price
  sorts work and stay applied when switching between Women, Men and Accessories.
- **Correct?** Yes. Small note: "Newest" just keeps the original order from `data.js`, because there is no date field until the backend exists.
- **What I learned:** With the rules in `AGENTS.md` and a scoped Frontend agent, the agent stayed inside `frontend/` and followed the project's patterns.

### 2026-10-15: Scaffold Milestone 1 (structure, SPA, ERD, docs)
- **Tool / mode:** Claude (Claude Code agent, working in the project folder)
- **Task:** Create the frontend/backend structure, `.gitignore`, the hash-routed SPA with all views,
  the draft ERD, README, AGENTS.md, the three agents and the `update-ai-log` skill.
- **Prompt:** The full Milestone 1 requirements from the course page.
- **Result:** All files in this commit. The downloaded SB Admin 2 template was moved to `_template-reference/`
  (git-ignored) because an admin template did not fit a luxury storefront; the SPA was written from scratch on Bootstrap 5.3.
- **Correct?** Partly. See the two cases below.
- **What I learned:** The SPA only works over `http://` (XAMPP), because `fetch()` of the views is blocked on `file://`.

### 2026-10-15: AI was wrong: "Size guide" link sent the user back to Home
- **Tool / mode:** Claude agent
- **Task:** Product view with a size-guide modal.
- **Result:** The AI wrote `<a href="#" data-bs-toggle="modal">Size guide</a>`. In a hash-routed SPA, `href="#"`
  changes the hash to empty, the router treats that as an unknown view and jumps to `#home` instead of opening the modal.
- **Correct?** No. Fixed by changing it to `<button type="button" class="btn btn-link">`, and a rule
  "never use `href=\"#\"`" was added to AGENTS.md so the assistant does not repeat it.
- **What I learned:** Code that is fine on a normal website can break a SPA router; generated links need checking against how routing works.

### 2026-10-15: AI was wrong: link colours ignored the theme
- **Tool / mode:** Claude agent
- **Task:** Black / ivory / gold theme on top of Bootstrap.
- **Result:** The AI overrode `--bs-link-color` and assumed links would turn black. Bootstrap 5.3 actually reads
  `--bs-link-color-rgb`, so breadcrumbs and product names in the bag stayed Bootstrap blue. It was only noticed
  when checking the page in the browser.
- **Correct?** No. Fixed with explicit `a { color: var(--mn-black) }` and `a:hover` rules in `css/style.css`.
- **What I learned:** Always look at the result in the browser; the AI's explanation of a CSS change is not proof that it works.
