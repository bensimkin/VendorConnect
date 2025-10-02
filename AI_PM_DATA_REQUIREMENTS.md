# AI Project Manager: Data Collection Requirements

**Project:** VendorConnect AI Project Manager  
**Version:** 1.0  
**Date:** September 30, 2025

---

## Overview

This document outlines all data points required for the AI Project Manager to function. VendorConnect currently collects some but not all of this data. Items marked with ⚠️ require new tracking infrastructure (Phase 0).

---

## 1. Task View Tracking ⚠️ **NEW**

### What We Need
- When users open/view task detail pages
- How many times each task has been viewed
- First view timestamp per user per task
- Optional: How long they viewed the task

### Why We Need It
- **Detect unopened tasks**: Flag tasks assigned 3+ days ago but never viewed by assignee
- **Measure engagement**: Understand if users are actively looking at their assignments
- **Identify disengagement**: Users who stop viewing tasks may be overwhelmed or disinterested

### How It Will Be Used
- Issue detection: "Task #127 assigned to John 5 days ago but never opened"
- Accountability metrics: "Mike hasn't viewed 4 of his 8 assigned tasks"
- Nudge trigger: Auto-send reminder if task unopened for 3+ days

### Implementation
**New Table:** `task_views`
- `task_id` - Which task was viewed
- `user_id` - Who viewed it
- `viewed_at` - When they viewed it
- `view_duration_seconds` - Optional: How long (tracking time on page)

---

## 2. Task Rejection Tracking ⚠️ **NEW**

### What We Need
- When users reject task assignments
- Reason for rejection (dropdown or text)
- Rejection note (optional detailed explanation)
- Rejection frequency per user

### Why We Need It
- **Identify workload issues**: Users rejecting multiple tasks may be overloaded
- **Spot misalignment**: Tasks assigned to wrong people (wrong skillset, unavailable)
- **Improve assignment**: Learn which users reject which types of tasks
- **Pattern detection**: "Design tasks consistently rejected by 3 different users"

### How It Will Be Used
- Issue detection: "Sarah has rejected 4 tasks in last 30 days"
- Assignment intelligence: Don't auto-assign similar tasks to users who reject them
- Workload management: Flag users with high rejection rate for check-in

### Implementation
**New Table:** `task_rejections`
- `task_id` - Which task was rejected
- `user_id` - Who rejected it
- `reason` - Brief reason code (e.g., "too_busy", "wrong_skillset", "unavailable")
- `rejection_note` - Optional detailed explanation
- `rejected_at` - When rejection occurred

---

## 3. Platform Usage Tracking ⚠️ **NEW**

### What We Need
- User login timestamps
- User logout timestamps (or session timeout)
- Session duration (how long they were active)
- Last activity timestamp
- IP address and user agent (optional, for security)

### Why We Need It
- **Detect declining engagement**: User's time on platform drops 40%+ from baseline
- **Identify inactive users**: Users with active tasks who haven't logged in for 5+ days
- **Measure team activity**: Overall platform usage trends
- **Spot burnout signals**: Users logging in less frequently despite active workload

### How It Will Be Used
- Issue detection: "Mike normally logs in daily, hasn't logged in for 6 days, has 5 active tasks"
- Usage patterns: "Team activity down 30% this week"
- Accountability metrics: Average session duration per user
- Nudge trigger: If user hasn't logged in for 5 days, send reminder

### Implementation
**New Table:** `user_sessions`
- `user_id` - Who logged in
- `session_token` - Unique session identifier
- `login_at` - When they logged in
- `logout_at` - When they logged out (or session expired)
- `last_activity_at` - Last action during session
- `duration_seconds` - Total session time
- `ip_address` - Where they logged in from (optional)
- `user_agent` - Browser/device info (optional)

---

## 4. User Activity per Task ⚠️ **NEW**

### What We Need
- Last activity timestamp for each user on each task they're assigned to
- Track any interaction: viewing, commenting, uploading, editing

### Why We Need It
- **Detect task abandonment**: User opened task but no activity for 5+ days
- **Measure responsiveness**: How quickly users engage after assignment
- **Identify stalled work**: Task has assignees but no recent activity

### How It Will Be Used
- Issue detection: "Task #145 has had no activity from any assignee in 7 days"
- Accountability metrics: Average time from assignment to first activity
- Risk identification: Tasks approaching deadline with no recent activity

### Implementation
**Table Modification:** Add column to existing `task_user` pivot table
- `last_activity_at` - Timestamp of last interaction with this task by this user
- Updated whenever user: views task, adds comment, uploads file, changes status, etc.

---

## 5. Historical Project Baselines ⚠️ **NEW**

### What We Need
- Average project duration (start to completion) across all completed projects
- Average project duration by client
- Average project duration by project type (if applicable)
- Average number of tasks per project
- Average task completion velocity (tasks per day)

### Why We Need It
- **Detect slow projects**: "Project ABC taking 30%+ longer than historical average"
- **Set realistic expectations**: Use historical data for better time estimates
- **Compare performance**: Current projects vs. past projects
- **Identify efficiency changes**: Are projects getting faster or slower over time?

### How It Will Be Used
- Issue detection: "Video project typically takes 14 days, this one is on day 21"
- AI insights: "Projects for this client historically take 20% longer than average"
- Benchmarking: "Your team's velocity is 15% faster than 6 months ago"

### Implementation
**New Table:** `project_metrics_baseline`
- `metric_name` - What metric (e.g., "avg_duration_days", "avg_task_count")
- `metric_value` - The average value
- `sample_size` - How many projects in the average
- `project_type` - Optional: filter by type
- `client_id` - Optional: filter by client
- `calculated_at` - When this baseline was calculated

**One-Time Script:** Calculate baselines from existing completed projects  
**Ongoing:** Recalculate monthly as new projects complete

---

## 6. Comment Activity Patterns ✅ **EXISTING** (just needs indexing)

### What We Need
- Timestamps of all task comments/messages
- Efficient querying by task and date range

### Why We Need It
- **Detect engagement drop**: Team communication decreased 60%+ from baseline
- **Measure collaboration**: How often team members update each other
- **Identify abandoned tasks**: No comments in 7+ days despite active status

### How It Will Be Used
- Issue detection: "Project team communication down 70% from average"
- Activity tracking: Recent comments indicate active work vs. stale tasks

### Implementation
**Existing Table:** `ch_messages` (already exists)
- Just needs performance index: `INDEX (to_id, created_at)` for efficient task message queries

---

## 7. Task Metadata & Relationships ✅ **EXISTING**

### What We Need (Already Collected)
- Task status (In Progress, Blocked, Completed, etc.)
- Task priority (High, Medium, Low)
- Task deadlines (`start_date`, `end_date`, `close_deadline`)
- Task assignments (who is assigned via `task_user` table)
- Checklist data (`template_checklist` field)
- Deliverable count (`deliverable_quantity` field)
- Parent-child task relationships (`parent_task_id`)
- Task creation and update timestamps
- Task notes and descriptions
- Project association

### Why We Need It
- Core data for all AI analysis
- Task status distribution
- Deadline tracking (overdue, approaching)
- Checklist completion calculation
- Priority-based risk assessment
- Subtask dependency checking

### How It Will Be Used
- Issue detection: Overdue tasks, blocked tasks, unassigned high-priority
- Compliance checking: Incomplete checklists, missing subtasks
- Progress tracking: % complete, velocity calculations

---

## 8. Checklist Completion Tracking ✅ **EXISTING**

### What We Need (Already Collected)
- Checklist items per task (from template)
- Which items are completed
- Who completed each item
- When items were completed

### Why We Need It
- **Track detailed progress**: % checklist complete per task
- **Compliance checking**: Ensure all items complete before marking task done
- **Accountability**: Who completes checklists on time

### How It Will Be Used
- Issue detection: "Checklist only 20% complete, deadline in 2 days"
- Compliance gate: Block task completion if checklist incomplete
- Accountability metrics: User checklist completion rate

### Implementation
**Existing Table:** `checklist_answered` (already exists)
- Links tasks to completed checklist items

---

## 9. Deliverable Tracking ✅ **EXISTING**

### What We Need (Already Collected)
- Files/deliverables uploaded to tasks
- Upload timestamps
- Who uploaded
- Expected deliverable count vs. actual

### Why We Need It
- **Detect incomplete work**: Task marked done but no deliverables uploaded
- **Compliance checking**: Expected 5 deliverables, only 2 uploaded
- **Track submission patterns**: Late submissions, missing files

### How It Will Be Used
- Issue detection: "Task requires attachments but none uploaded"
- Compliance gate: Block completion if deliverable count doesn't match
- Pattern analysis: "Design tasks consistently missing approval documents"

### Implementation
**Existing Table:** `task_deliverables` (already exists)
- Already tracks file uploads per task

---

## 10. User Login Tracking ✅ **EXISTING** (partial)

### What We Need (Already Partially Collected)
- Last login timestamp per user

### Current State
- VendorConnect tracks `last_login_at` in `users` table
- This is updated on each login
- **Missing:** Session duration, logout time, activity patterns

### Why We Need More Detail
- Current timestamp only tells us last login, not usage patterns
- Can't calculate session duration or engagement time
- Can't detect declining usage trends

### How It Will Be Used (Current)
- Issue detection: "User hasn't logged in for 5+ days" (basic check)
- **With enhanced tracking:** Detailed engagement patterns, session trends

---

## 11. Project Metadata ✅ **EXISTING**

### What We Need (Already Collected)
- Project status
- Project start and end dates
- Project budget
- Project description and notes
- Client association
- Team member assignments
- Task count per project

### Why We Need It
- Project-level health scoring
- Duration analysis (actual vs. planned)
- Team workload calculation
- Client-specific patterns

### How It Will Be Used
- Issue detection: Project health score calculation
- Baseline comparison: Current vs. historical durations
- Resource allocation: Team capacity analysis

---

## 12. Blocker Keyword Detection ⚠️ **NEW ANALYSIS**

### What We Need
- NLP analysis of task comments, notes, descriptions
- Scan for keywords: "blocked", "waiting", "stuck", "can't proceed", "need help", "waiting for"

### Why We Need It
- **Detect blockers early**: Users mention blockers in comments before formally changing status
- **Proactive intervention**: Catch "stuck" tasks before they become critical
- **Pattern recognition**: Common blocker types across projects

### How It Will Be Used
- Issue detection: "Task comments contain 'blocked' keyword"
- Compliance check: Don't allow completion if recent blocker keywords detected
- Nudge trigger: Send blocker follow-up email to task creator and assignee
- AI insight: "Most common blocker this month: 'waiting for client approval'"

### Implementation
- **No new table needed** - analyze existing `ch_messages` and `tasks.note` fields
- Run NLP scan during AI analysis
- Simple keyword matching initially, can enhance with semantic analysis later

---

## 13. Attachment Validation ⚠️ **NEW ANALYSIS**

### What We Need
- Determine which tasks require attachments (briefs, assets, approvals)
- Currently stored in task description or template
- Need structured way to flag "attachment required" tasks

### Why We Need It
- **Compliance checking**: Ensure required files uploaded before completion
- **Quality assurance**: Don't let tasks slip through without necessary documents
- **Pattern detection**: "Team consistently forgets to upload client briefs"

### How It Will Be Used
- Issue detection: "Task requires attachments but media count = 0"
- Compliance gate: Block completion if required attachments missing
- Nudge reminder: "Don't forget to upload client approval before completing"

### Implementation Options
1. **Add field to tasks table**: `requires_attachments` boolean
2. **Use template metadata**: Store in `template_checklist` or `template_questions`
3. **NLP detection**: Scan task description for words like "upload", "attach", "submit file"
4. **Start simple**: Flag tasks where `deliverable_quantity > 0` as requiring attachments

---

## Data Summary Table

| Data Type | Status | Implementation | Priority | Usage |
|-----------|--------|----------------|----------|-------|
| **Task Views** | ⚠️ NEW | New `task_views` table + frontend tracking | P0 | Unopened task detection |
| **Task Rejections** | ⚠️ NEW | New `task_rejections` table + UI workflow | P0 | Workload management |
| **User Sessions** | ⚠️ NEW | New `user_sessions` table + login/logout tracking | P0 | Engagement patterns |
| **Activity Timestamps** | ⚠️ NEW | Add `last_activity_at` to `task_user` | P0 | Task abandonment |
| **Historical Baselines** | ⚠️ NEW | New `project_metrics_baseline` + calculation script | P0 | Duration comparison |
| **Comment Timestamps** | ✅ EXISTS | Just add index to `ch_messages` | P0 | Communication trends |
| **Task Metadata** | ✅ EXISTS | Already in `tasks` table | P0 | Core analysis |
| **Checklist Data** | ✅ EXISTS | Already in `checklist_answered` | P0 | Compliance checking |
| **Deliverables** | ✅ EXISTS | Already in `task_deliverables` | P0 | Attachment validation |
| **User Logins** | ⚠️ PARTIAL | Enhance `users.last_login_at` with sessions | P0 | Basic activity tracking |
| **Project Data** | ✅ EXISTS | Already in `projects` table | P0 | Project health |
| **Blocker Keywords** | ⚠️ NEW | NLP analysis of existing comments | P1 | Proactive blocker detection |
| **Attachment Requirements** | ⚠️ NEW | Field or template metadata | P1 | Compliance validation |

---

## Collection Strategy

### Phase 0 (Must Build First)
1. **Week 1:**
   - Implement task view tracking
   - Add comment activity indexing

2. **Week 2:**
   - Implement task rejection tracking
   - Add activity timestamps to task_user

3. **Week 3:**
   - Implement user session tracking
   - Calculate historical baselines
   - Run initial baseline calculation

### Phase 1 (AI Analysis)
Once Phase 0 data is collecting for 2-4 weeks:
- AI can analyze trends
- Sufficient data for baseline comparisons
- Meaningful engagement patterns emerge

---

## Data Retention

### Short-Term (Active Use)
- **Task views**: Keep for 90 days (then archive)
- **Task rejections**: Keep indefinitely (useful for patterns)
- **User sessions**: Keep for 180 days (then archive)
- **Activity timestamps**: Keep while task is active + 30 days
- **Baselines**: Keep all historical calculations

### Long-Term (Archive)
- Archive old task views to separate table after 90 days
- Archive old sessions after 180 days
- Maintain baseline history indefinitely (small data footprint)

---

## Privacy & Security

### Personal Data
- **User sessions**: IP addresses are optional, can be disabled
- **All tracking**: Anonymize in AI prompts if privacy mode enabled
- **User consent**: Users can opt-out of automated nudges
- **Data access**: Only admins can view detailed user metrics

### GDPR Compliance
- Users can request data export (all their tracking data)
- Users can request data deletion
- Clear privacy policy explaining what's tracked and why

---

## Performance Considerations

### Query Optimization
- **Indexes required**: See implementation guide for all index definitions
- **Expected data volume**:
  - Task views: ~1,000 per day (depends on active users)
  - Sessions: ~50 per day (depends on team size)
  - Rejections: ~10 per week (hopefully low)
  - Baselines: ~20 records total (recalculated monthly)

### Storage Impact
- **Task views**: ~1MB per 10,000 rows
- **User sessions**: ~500KB per 10,000 rows
- **Total new data**: ~2-5MB per month initially
- **Minimal impact**: Modern MySQL handles this easily

---

## Success Criteria

### Data Collection is Successful When:
- [ ] Task views logging consistently (>90% of page loads tracked)
- [ ] Sessions tracked for all logins (100% coverage)
- [ ] Historical baselines calculated from at least 10 completed projects
- [ ] No performance degradation (<50ms overhead)
- [ ] Data quality high (no missing timestamps, valid foreign keys)
- [ ] Queries execute in <100ms (with proper indexes)

### Ready for AI Analysis When:
- [ ] 2+ weeks of task view data collected
- [ ] 2+ weeks of session data collected
- [ ] Baselines calculated and stored
- [ ] Sufficient data volume for meaningful trends (>100 task views, >50 sessions)

---

## Next Steps

1. **Review this document** - Ensure all stakeholders understand what data we're collecting and why
2. **Review Phase 0 Implementation Guide** - Detailed technical implementation steps
3. **Start data collection** - Even if AI PM isn't built yet, start collecting data now
4. **Monitor data quality** - Weekly checks during first month
5. **Build AI PM** - Once 2-4 weeks of quality data collected

---

**Questions or Concerns?**  
Contact: Benjamin Simkin | Project: VendorConnect AI Project Manager
