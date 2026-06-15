# Project Rules & Guidelines for AI Assistant
> [!CRITICAL]
> **The AI MUST use the `view_file` tool to read this entire document from top to bottom before doing anything else in a new session.**
This document contains persistent rules that the AI assistant must follow when working on this project.

## 1. Workspace Awareness & Active Folder Detection
**CRITICAL RULE:** The user may have multiple projects or tabs open. You MUST dynamically detect the active project folder by checking the `Active Document` path in the system metadata at the start of the session. 
1. **Scope of Work**: Only apply rules, run commands, and edit files within the detected active workspace folder.
2. **State Active Folder**: In your very first reply of a new session, you MUST explicitly output the path or name of the folder you have detected as the active workspace.
3. **State Automations**: In your very first reply of a new session, you MUST explicitly list the Automations (e.g., "New Task Trigger", "Sign Off Command") found in this document so that you and the user are perfectly aligned on what background tasks are active.

## 2. Always Create a Local Git Backup
**CRITICAL RULE:** Before writing any new code, building a new feature, or attempting any major refactoring, you MUST run a local git commit to back up the current stable state of the project. 

**Instructions for AI:**
1. Run `git status` to check for any uncommitted work.
2. If there is uncommitted work (code that hasn't been locked in yet), run `git add .` and `git commit -m "Feat: <short description of the task you just finished>"` to safely lock it into Git history with a descriptive message.
3. Only AFTER the local commit is successfully created may you begin modifying the codebase.
4. If your new changes break the system, use Git to revert your changes back to the safe commit rather than trying to manually undo your code line-by-line.
5. Acknowledge these rules: Every time you start a new conversation, your first message MUST include a brief confirmation that you have read the rules, stating the active workspace, and explicitly listing the automations.
6. **Backup Before Changes**: You MUST ALWAYS create a local git commit of the current state BEFORE you make any significant changes or begin a new task. Run `git add .` and `git commit -m "Feat: <short description of previous changes>"`. Never skip this step. This ensures we can easily rollback if an experiment fails. Do not ask the user for permission to do this, just do it.

## Automations

1. **New Task Trigger**: If the user says "new task", you MUST automatically run `git add .` and `git commit -m "Feat: <short description of previous changes>"` BEFORE discussing or planning the new feature.

2. **Sign Off Command**: If the user says "sign off" (or similar), you MUST automatically:
   - Generate a markdown file named `session_log_YYYY-MM-DD.md` in the project root.
   - Summarize all the work done, bugs fixed, and exact technical choices made during the current session into that file.
   - **CRITICAL**: Do NOT run `git add` or `git commit` for this file. Leave it as a purely local file. The user will use this file to quickly bring the AI up to speed in the next session by dragging and dropping it into the chat.
