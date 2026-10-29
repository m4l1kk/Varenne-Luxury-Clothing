---
name: update-ai-log
description: Add an entry to docs/AI_LOG.md describing how an AI assistant was used for a change. Use after any AI-assisted change, or when the user says "log this", "update the AI log" or "add to AI_LOG".
---

# update-ai-log

Adds one entry to `docs/AI_LOG.md` under the section for the current milestone.

## Steps
1. Find the current milestone from the git branch name (`milestone1` → "Milestone 1"). If the section
   `## Milestone N` does not exist in `docs/AI_LOG.md`, create it at the end of the file.
2. Ask the user (or infer from the conversation) for:
   - **Tool and mode**: e.g. Copilot completions, Copilot Ask, Copilot agent mode, Antigravity agent.
   - **Task**: what the AI was asked to do, in one sentence.
   - **Prompt**: the main prompt, shortened if long.
   - **Result**: what the AI produced and which files changed.
   - **Was it correct?** `Yes`, `Partly` or `No`. If not fully correct, what was wrong and how it was fixed.
   - **What I learned**: one sentence.
3. Append the entry using this template, keeping entries in date order:

```markdown
### YYYY-MM-DD: <short title>
- **Tool / mode:** ...
- **Task:** ...
- **Prompt:** "..."
- **Result:** ...
- **Correct?** Yes | Partly | No. <what was wrong and how it was fixed>
- **What I learned:** ...
```

4. Do not change or delete earlier entries. Do not include secrets, passwords or tokens in the log.
5. Show the new entry to the user so they can confirm it is accurate. The log is the student's own account,
   so never invent experiences the user did not have.
