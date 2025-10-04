# Product Requirements Document: AI Project Manager

**Product Name:** VendorConnect AI Project Manager  
**Version:** 1.0  
**Document Status:** Draft  
**Last Updated:** September 30, 2025  
**Author:** Benjamin Simkin  
**Stakeholders:** Admin Users, Project Requesters

---

## Executive Summary

The AI Project Manager is an intelligent oversight feature that acts as a virtual project manager within VendorConnect. It autonomously monitors project health, task completion, checklist progress, user engagement patterns, and identifies potential issues across all active projects. Using OpenAI's language models, it provides natural language insights, proactive recommendations, and automated status reports to Admin users through both a dedicated dashboard section and weekly email digests.

**⚠️ CRITICAL PREREQUISITE:** VendorConnect currently does **NOT** collect most of the data needed for enhanced monitoring features (task views, rejections, platform usage patterns, historical baselines). **Phase 0 (3 weeks) must build this tracking infrastructure first** before the AI can analyze it. See Section 5.1 for details.

**Phase 2 Enhancement:** AI-powered follow-up email generator that allows admins to select detected issues, automatically generate professional follow-up emails, customize content, and send to team members - saving 15+ minutes per follow-up.

### Key Value Proposition
- **Proactive Issue Detection**: Identifies 24+ issue types including rejected tasks, low engagement, velocity decline, and blocker keywords (NLP) before they become critical
- **Automated Oversight**: Reduces manual project monitoring overhead for admins by 70%
- **Smart Compliance Checker**: Automated QA ensures checklists complete, attachments uploaded, and subtasks finished before marking done
- **Intelligent Insights**: Leverages AI to surface patterns, compare to historical averages, and provide actionable recommendations
- **Enhanced Accountability**: Per-person heat map with individual performance metrics, on-time rates, and workload visibility
- **Automated Nudges**: Gentle email reminders sent directly to team members for overdue tasks, approaching deadlines, and unopened assignments
- **Executive Visibility**: Provides high-level overview with positive/negative signals and deep-dive capabilities
- **Smart Follow-Up (Phase 2)**: Generate personalized follow-up emails with AI in seconds instead of writing manually

### Enhanced Monitoring Capabilities

The AI Project Manager monitors a comprehensive set of signals to provide complete project visibility:

**📊 Traditional Project Metrics:**
- Task completion rates and deadline adherence
- Checklist completion percentages
- Priority distribution and deliverables

**👤 User Engagement Metrics:**
- **Task Rejection Tracking** - Who's rejecting tasks and why
- **Task View Engagement** - Tasks assigned but never opened by users
- **Platform Usage Patterns** - Login frequency, time on platform, activity trends
- **Response Time Analysis** - How quickly users engage with assigned tasks

**📈 Advanced Analytics:**
- **Historical Comparisons** - Project duration vs. historical averages (detect slowdowns)
- **Velocity Tracking** - Task completion pace and momentum trends
- **Progression Indicators** - Actual work progress vs. time elapsed
- **Communication Analysis** - Team engagement through comments and updates

**🚦 Positive & Negative Signals:**
- ✅ **Positive:** Ahead of schedule, high engagement, proactive updates, consistent velocity
- ⚠️ **Negative:** Declining activity, missed deadlines, low engagement, task abandonment, stalled projects

This holistic approach ensures admins catch issues early and recognize what's working well.

---

## Problem Statement

### Current Challenges
1. **Manual Oversight Burden**: Admins must manually review multiple projects to identify issues
2. **Delayed Problem Detection**: Issues may go unnoticed until they become critical
3. **Scattered Information**: Project health indicators are spread across multiple views
4. **Reactive Management**: System lacks proactive alerting for at-risk projects
5. **Limited Intelligence**: No automated analysis of project patterns or trends
6. **Time-Intensive Reporting**: Creating status summaries requires manual data aggregation

### User Pain Points
- "I don't have time to check every project daily to see if tasks are on track"
- "By the time I notice a project is behind, we've already missed the deadline"
- "I wish I had a weekly summary of what needs my attention"
- "It's hard to spot patterns across multiple projects without manual analysis"

---

## Goals & Objectives

### Primary Goals
1. Provide automated, intelligent project oversight across the entire VendorConnect workspace
2. Reduce admin time spent on manual project status monitoring by 70%
3. Increase early detection of at-risk projects by 80%
4. Deliver actionable insights that improve project completion rates

### Success Metrics
- **Adoption**: 80% of admins actively use AI Project Manager dashboard within 3 months
- **Email Engagement**: 60% open rate on weekly AI PM email reports
- **Issue Detection**: AI identifies and flags issues 2-3 days earlier than current manual process
- **Time Savings**: Admins report 5+ hours saved per week on project monitoring
- **Project Health**: 15% improvement in on-time task completion rate

### Non-Goals (Out of Scope for v1.0)
- AI will not automatically take actions (only recommend actions)
- AI will not directly communicate with Taskers or Requesters
- AI will not create or modify tasks/projects automatically
- AI will not provide real-time chat interface (future consideration)
- AI will not replace human project managers (augmentation only)

---

## Target Users

### Primary User: Admin Role
- **Profile**: Business owners, operations managers, team leads
- **Responsibilities**: Oversee multiple projects, ensure timely delivery, resource allocation
- **Technical Proficiency**: Moderate to high
- **Usage Pattern**: Daily dashboard checks, weekly deep reviews
- **Pain Points**: Too many projects to monitor manually, needs executive summary

### Secondary User: Sub-Admin Role
- **Profile**: Department managers, senior project coordinators
- **Responsibilities**: Oversee subset of projects within their domain
- **Technical Proficiency**: Moderate
- **Usage Pattern**: Regular monitoring of assigned projects
- **Pain Points**: Needs quick overview of project health in their area

---

## Feature Requirements

### 1. AI Analysis Engine

#### 1.1 Data Collection & Analysis
**Priority:** P0 (Critical)

**Description:**  
The AI engine continuously monitors and analyzes key project metrics to assess health and identify issues.

**Monitored Metrics:**
- Task completion rates (completed vs. total tasks per project)
- Checklist completion percentages (completed items vs. total items)
- Deadline adherence (tasks on time, approaching deadlines, overdue)
- Task status distribution (by status type: In Progress, Blocked, Review, etc.)
- **Task rejection tracking** (tasks rejected by users, rejection reasons, rejection frequency)
- User response times (time between assignment and first action)
- **Task engagement tracking** (tasks assigned but never opened/viewed by user)
- **Project duration analysis** (time from creation to completion vs. historical averages)
- **Platform usage patterns** (user login frequency, time spent on platform, activity trends)
- Project velocity (tasks completed over time)
- Resource allocation (task distribution across users)
- Stale tasks (tasks with no recent activity)
- Priority distribution and high-priority task status
- Deliverable submission rates
- Comment/communication activity levels
- **Positive signals** (ahead-of-schedule tasks, high engagement, frequent updates, proactive communication)
- **Negative signals** (declining activity, missed deadlines, low engagement, task abandonment)
- **Project progression indicators** (actual work progress vs. elapsed time, velocity trends, momentum indicators)
- **Blocker detection via NLP** (scan task notes/comments for words like "waiting," "blocked," "stuck," "can't proceed")
- **Missing attachments detection** (tasks missing required briefs, assets, or approvals)
- **Incomplete subtasks** (parent tasks marked done but child tasks incomplete)
- **Unassigned task detection** (tasks with no assigned owner, especially near deadlines)

**Analysis Frequency:**
- **Real-time data collection**: Continuous via database triggers/events
- **AI analysis runs**: Every 6 hours for active projects
- **Weekly report generation**: Scheduled for Monday mornings at 8 AM local time
- **Daily quick pulse** (optional): Quick health check for high-priority projects only
- **On-demand analysis**: Admin can trigger manual analysis anytime with "Run AI Project Check Now" button

**Technical Requirements:**
- Query optimization to handle large datasets efficiently
- Caching layer for frequently accessed metrics
- Background job processing (Laravel queues)
- Rate limiting for OpenAI API calls
- Error handling and retry logic for API failures

#### 1.2 OpenAI Integration
**Priority:** P0 (Critical)

**Description:**  
Integration with OpenAI API (GPT-4 or GPT-4-Turbo) to generate natural language insights and recommendations.

**Capabilities:**
- **Pattern Recognition**: Identify trends and patterns across projects
- **Risk Assessment**: Evaluate project health and predict potential issues
- **Recommendations**: Generate actionable suggestions for admins
- **Executive Summaries**: Create concise, readable reports from raw data
- **Comparative Analysis**: Compare current period vs. previous period
- **Root Cause Analysis**: Analyze why projects are behind or blocked

**API Configuration:**
- Model: GPT-4-Turbo (or GPT-4)
- Temperature: 0.3 (focused, consistent outputs)
- Max tokens: 1500 per analysis
- System prompt: Custom prompt engineering for project management context
- Fallback: If API fails, show raw metrics with graceful degradation

**Data Privacy:**
- Anonymize sensitive data before sending to OpenAI (optional setting)
- Store API keys securely in environment variables
- Comply with OpenAI usage policies
- Admin settings to control what data gets analyzed by AI

**Prompt Engineering Strategy:**
```
You are an experienced project manager analyzing project health data.
Provide concise, actionable insights based on the following metrics:

[PROJECT DATA]
- Project: {project_name}
- Tasks: {completed}/{total} completed
- Overdue tasks: {overdue_count}
- Checklist completion: {checklist_percentage}%
- Last activity: {last_activity_date}
- Priority tasks at risk: {priority_at_risk}

Analyze:
1. Overall project health (rate 1-10)
2. Key issues or blockers
3. Specific recommendations (max 3)
4. What needs immediate attention
```

#### 1.3 Issue Detection Rules
**Priority:** P0 (Critical)

**Description:**  
Automated detection rules that flag projects/tasks requiring attention.

**Issue Types & Thresholds:**

| Issue Type | Detection Rule | Severity |
|------------|---------------|----------|
| **Stale Project** | No task activity in 7+ days | Medium |
| **Overdue Tasks** | Any task past end_date | High |
| **Approaching Deadline** | Task due within 48 hours, <50% checklist complete | Medium |
| **Blocked Tasks** | Task in "Blocked" status for 3+ days | High |
| **Low Checklist Completion** | Project >50% through timeline, <30% checklists done | Medium |
| **Unassigned Tasks** | Tasks with approaching deadlines but no assigned user | High |
| **High Priority At Risk** | High priority task overdue or behind schedule | Critical |
| **Low Team Activity** | Assigned users haven't logged in for 5+ days | Medium |
| **Incomplete Deliverables** | Task marked complete but no deliverables uploaded | Medium |
| **Pattern: Repeated Delays** | Project has 3+ deadline extensions | Low |
| **Rejected Tasks** | Task rejected by assigned user | Medium |
| **High Rejection Rate** | User has rejected 3+ tasks in last 30 days | High |
| **Unopened Tasks** | Task assigned 3+ days ago but never viewed by user | High |
| **Slow Project Duration** | Project taking 30%+ longer than historical average | Medium |
| **Declining Platform Activity** | User's time on platform decreased 40%+ from baseline | Medium |
| **Task Abandonment** | User opened task but no activity for 5+ days | Medium |
| **No Project Progression** | Project at same % complete for 7+ days despite activity | High |
| **Velocity Decline** | Project velocity dropped 50%+ compared to first half | High |
| **Engagement Drop** | Team communication/comments decreased 60%+ | Medium |
| **Blocker Keywords Detected** | Task notes contain "blocked," "waiting," "stuck," "can't proceed" | High |
| **Missing Attachments** | Task requires attachments but none uploaded (briefs, assets, approvals) | Medium |
| **Parent Task Closed Prematurely** | Parent task marked complete but child tasks incomplete | High |
| **Unassigned Critical Task** | High-priority task has no assigned owner | Critical |
| **Compliance Check Failed** | Checklist incomplete but project stage marked "done" | High |

**Severity Levels:**
- **Critical**: Requires immediate action, likely impacting delivery
- **High**: Needs attention within 24 hours
- **Medium**: Monitor and address within 3-5 days
- **Low**: Good to know, not urgent

---

#### 1.4 Smart Compliance Checker
**Priority:** P0 (Critical)

**Description:**  
Automated quality assurance system that ensures project stages and tasks meet completion requirements before being marked as "done."

**Compliance Checks:**

| Check Type | Rule | Action When Failed |
|------------|------|-------------------|
| **Checklist Completion Gate** | All checklist items must be completed before project stage can be marked "done" | Block status change + alert admin |
| **Attachment Validation** | Tasks requiring attachments (briefs, assets, approvals) must have files uploaded | Flag as "Incomplete Deliverables" |
| **Subtask Dependency** | Parent tasks cannot be marked complete if child tasks are incomplete | Flag as "Parent Task Closed Prematurely" |
| **Owner Assignment** | High-priority tasks must have at least one assigned owner | Flag as "Unassigned Critical Task" |
| **Blocker Resolution** | Tasks with blocker keywords in recent comments should not be marked complete | Flag as "Blocker Keywords Detected" |
| **Deliverable Count** | If task specifies `deliverable_quantity`, that many deliverables must be uploaded | Flag as "Missing Deliverables" |

**Implementation:**

```php
// Pseudo-code for compliance check
function checkTaskCompliance(Task $task): array
{
    $violations = [];
    
    // Check 1: Checklist completion
    if ($task->hasIncompleteChecklists()) {
        $violations[] = [
            'type' => 'incomplete_checklist',
            'severity' => 'high',
            'message' => 'Task has incomplete checklist items'
        ];
    }
    
    // Check 2: Required attachments
    if ($task->requiresAttachments() && $task->attachments()->count() === 0) {
        $violations[] = [
            'type' => 'missing_attachments',
            'severity' => 'medium',
            'message' => 'Task marked as requiring attachments but none uploaded'
        ];
    }
    
    // Check 3: Subtask completion
    if ($task->childTasks()->incomplete()->count() > 0) {
        $violations[] = [
            'type' => 'incomplete_subtasks',
            'severity' => 'high',
            'message' => 'Parent task has ' . $task->childTasks()->incomplete()->count() . ' incomplete subtasks'
        ];
    }
    
    // Check 4: Owner assignment
    if ($task->priority->is_high && $task->users()->count() === 0) {
        $violations[] = [
            'type' => 'no_owner',
            'severity' => 'critical',
            'message' => 'High-priority task has no assigned owner'
        ];
    }
    
    // Check 5: Blocker keywords (NLP)
    $blockerKeywords = ['blocked', 'waiting', 'stuck', 'can\'t proceed', 'need help'];
    $recentComments = $task->messages()->recent(7)->pluck('body')->join(' ');
    foreach ($blockerKeywords as $keyword) {
        if (stripos($recentComments, $keyword) !== false) {
            $violations[] = [
                'type' => 'blocker_detected',
                'severity' => 'high',
                'message' => "Recent comments contain blocker keyword: '{$keyword}'"
            ];
            break;
        }
    }
    
    // Check 6: Deliverable count
    if ($task->deliverable_quantity && $task->deliverables()->count() < $task->deliverable_quantity) {
        $violations[] = [
            'type' => 'insufficient_deliverables',
            'severity' => 'medium',
            'message' => 'Expected ' . $task->deliverable_quantity . ' deliverables, found ' . $task->deliverables()->count()
        ];
    }
    
    return $violations;
}
```

**User Experience:**

When admin or user attempts to mark task/project as complete:
1. Run compliance check
2. If violations found:
   - Show modal with list of violations
   - Prevent status change (for critical/high violations)
   - Allow override with admin confirmation
3. Log violation attempt for AI analysis

**AI Integration:**

Compliance violations feed into AI analysis:
- "Project XYZ has 3 tasks with compliance issues"
- "Team consistently forgets to upload attachments (5 instances this week)"
- "Pattern detected: Design tasks marked complete before client approval uploaded"

---

### 2. Admin Dashboard Section

#### 2.1 AI Project Manager Dashboard Widget
**Priority:** P0 (Critical)

**Description:**  
Dedicated section on the admin dashboard displaying AI-generated insights and project health overview.

**Location:**  
- Main Admin Dashboard (`/dashboard`)
- Prominent placement in top third of page
- Expandable/collapsible card design
- Always visible to Admin and Sub-Admin roles

**Dashboard Components:**

##### A. Executive Summary Card
- **Header**: "AI Project Manager Weekly Report"
- **Content**:
  - Overall health score (1-10 with color indicator)
  - Total projects monitored
  - Issues detected (count by severity)
  - Key recommendation (top priority action)
  - Last analysis timestamp
- **Visual**: Traffic light colors (Green/Yellow/Red) for health status
- **CTA Button**: "View Full Report"

##### B. Critical Alerts Section
- **Display**: List of critical issues requiring immediate attention
- **Format**:
  - Project name (clickable link)
  - Issue type badge (color-coded by severity)
  - Brief AI-generated description (1-2 sentences)
  - "View Details" button
- **Limit**: Show top 5 critical issues
- **Empty State**: "🎉 No critical issues detected! All projects on track."

##### C. Project Health Overview
- **Visualization**: Grid/list of all active projects with health indicators
- **Columns**:
  - Project Name
  - Health Score (visual meter 0-10)
  - Status Badge (On Track / At Risk / Behind)
  - Tasks Complete (progress bar)
  - Checklist Complete (percentage)
  - Next Deadline (countdown)
- **Sorting**: Default by health score (worst first), user can change
- **Filtering**: By status, client, date range
- **Click Action**: Expand to show AI insights for that project

##### D. AI Insights Panel (Expandable)
- **Trigger**: Clicking "View Full Report" or project row
- **Content**:
  - AI-generated executive summary (2-3 paragraphs)
  - Detailed issue breakdown by project
  - Trend analysis (comparing to previous week)
  - Top 5 recommendations prioritized
  - "Ask AI" prompt box (future enhancement placeholder)
- **Format**: Natural language, conversational tone
- **Actions**: 
  - "Email This Report to Me"
  - "Export as PDF"
  - "Mark as Reviewed"

##### E. Trends & Analytics (Optional for v1.0)
- **Charts**:
  - Weekly task completion velocity graph
  - Project health score trend over time
  - Most common issue types (pie chart)
  - Team performance metrics
- **Period**: Last 4 weeks, 3 months, 6 months

##### F. Per-Person Accountability View **NEW**
**Priority:** P1 (High)

**Description:**  
Individual performance metrics and accountability dashboard for each team member.

**View Layout:**

```
┌─────────────────────────────────────────────────────────────┐
│ Team Accountability Overview                          [Filter]│
├─────────────────────────────────────────────────────────────┤
│                                                              │
│ ┌────────────┬────────────┬────────────┬────────────┐      │
│ │ Sarah M.   │ John D.    │ Mike T.    │ Emily R.   │      │
│ │ ──────────│ ──────────│ ──────────│ ──────────│      │
│ │ 🟢 92%     │ 🟡 78%     │ 🔴 45%     │ 🟢 88%     │      │
│ │ On-time    │ On-time    │ On-time    │ On-time    │      │
│ │            │            │            │            │      │
│ │ 5/5 ✅     │ 7/9 ⚠️     │ 3/8 ❌     │ 8/9 ✅     │      │
│ │ Tasks      │ Tasks      │ Tasks      │ Tasks      │      │
│ │            │            │            │            │      │
│ │ 0 Overdue  │ 2 Overdue  │ 5 Overdue  │ 1 Overdue  │      │
│ │ 3 Active   │ 5 Active   │ 6 Active   │ 4 Active   │      │
│ └────────────┴────────────┴────────────┴────────────┘      │
│                                                              │
│ 🔥 Heat Map: Who Needs Support                              │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ Sarah M.  ▓░░░░░░░░░ (Excellent)                    │   │
│ │ John D.   ▓▓▓▓░░░░░░ (Good)                         │   │
│ │ Mike T.   ▓▓▓▓▓▓▓▓░░ (Needs Support) 🚨             │   │
│ │ Emily R.  ▓▓░░░░░░░░ (Good)                         │   │
│ └──────────────────────────────────────────────────────┘   │
│                                                              │
│ 🔍 Detailed Metrics (Click name to expand)                  │
│ ┌──────────────────────────────────────────────────────┐   │
│ │ ▼ Mike T. - Needs Attention                         │   │
│ │   • On-time completion rate: 45% (down from 78%)    │   │
│ │   • 5 tasks overdue (avg 4.2 days)                  │   │
│ │   • Checklist compliance: 60% (team avg 82%)        │   │
│ │   • Last login: 2 days ago                          │   │
│ │   • Pattern: Consistently delayed on design tasks   │   │
│ │                                                       │   │
│ │   💡 Suggested Actions:                              │   │
│ │   • 1-on-1 check-in to identify blockers            │   │
│ │   • Redistribute 2-3 tasks to reduce workload       │   │
│ │   • Review task complexity/time estimates           │   │
│ │                                                       │   │
│ │   [Send Reminder] [View Tasks] [Reassign Tasks]     │   │
│ └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

**Metrics Tracked Per Person:**

| Metric | Calculation | Purpose |
|--------|-------------|---------|
| **On-Time Completion Rate** | (Tasks completed by deadline / Total tasks) × 100 | Overall reliability |
| **Overdue Task Count** | Count of tasks past end_date | Current accountability |
| **Active Task Load** | Count of in-progress tasks assigned | Workload visibility |
| **Checklist Compliance Rate** | (Completed checklist items / Total items) × 100 | Detail-orientation |
| **Average Task Duration** | Mean time from assignment to completion | Efficiency |
| **Response Time** | Time between task assignment and first activity | Engagement level |
| **Communication Activity** | Comments/updates per task | Proactivity |
| **Rejection Rate** | Tasks rejected in last 30 days | Workload appropriateness |

**Heat Map Colors:**
- 🟢 Green (90-100%): Excellent performance
- 🟡 Yellow (70-89%): Good, minor attention may help
- 🟠 Orange (50-69%): Needs support
- 🔴 Red (0-49%): Requires immediate intervention

**Escalation Flags:**

AI automatically flags individuals who:
- On-time rate drops below 50%
- Have 3+ overdue tasks
- Haven't logged in for 5+ days with active tasks
- Consistently reject tasks (3+ in 30 days)
- Compliance rate below 60%

**Admin Actions:**

From this view, admin can:
1. **Send Reminder** - Trigger AI-generated reminder email to individual
2. **View Tasks** - See all tasks assigned to person
3. **Reassign Tasks** - Bulk reassign overdue tasks to others
4. **Schedule Check-In** - Create meeting/reminder for 1-on-1
5. **View Trend** - Historical performance over time

**Technical Requirements:**
- React/Next.js component in frontend
- API endpoint: `GET /api/ai-project-manager/dashboard`
- Real-time updates via polling (every 60 seconds) or WebSockets
- Skeleton loaders for AI content
- Error states with fallback to raw metrics
- Responsive design (mobile-friendly)

#### 2.2 Detailed Project Analysis View
**Priority:** P1 (High)

**Description:**  
Dedicated page for deep-dive AI analysis of individual projects.

**Route:** `/ai-project-manager/project/{project_id}`

**Content:**
- Project overview with AI health assessment
- Task-by-task breakdown with AI commentary
- Checklist completion analysis
- Timeline visualization with risk indicators
- User performance metrics for project team
- Recommended next actions
- Historical health score graph

**Access:**
- Clickable from dashboard project list
- Accessible via Projects page "AI Analysis" button

---

### 3. Automated Email Reports

#### 3.1 Weekly AI Project Manager Email
**Priority:** P0 (Critical)

**Description:**  
Automated email digest sent to Admin users every week with AI-generated project insights.

**Schedule:**
- **Frequency**: Weekly
- **Day/Time**: Monday mornings at 8:00 AM (user's local timezone)
- **Recipients**: All users with Admin or Sub-Admin role
- **Settings**: Users can opt-out via notification preferences

**Email Structure:**

**Subject Line:**  
`📊 Weekly AI Project Manager Report - [X] Issues Need Attention`

**Header Section:**
- VendorConnect logo
- Report date range (e.g., "Week of Sept 23-29, 2025")
- Greeting: "Hi {Admin Name},"

**Executive Summary:**
```
📈 Overall Project Health: 7.5/10 (Stable)

This week I monitored 12 active projects and 47 tasks. Here's what needs your attention:

🔴 3 Critical Issues
🟡 5 Medium-Priority Items  
🟢 8 Projects On Track
```

**Critical Issues Section:**
```
⚠️ NEEDS IMMEDIATE ATTENTION

1. Video Production - Client ABC
   Issue: 3 tasks overdue, deadline in 2 days
   Recommendation: Reassign overdue tasks to Sarah M. who has capacity
   [View Project →]

2. Website Redesign - Client XYZ
   Issue: Project stalled - no activity in 9 days
   Recommendation: Check in with assigned team, may need resources
   [View Project →]
```

**Key Insights Section:**
```
💡 KEY INSIGHTS

• Task completion velocity decreased 15% this week - team may be overallocated
• Checklist completion rate is strong at 82% across all projects
• Most common blocker: Waiting on client feedback (4 instances)
• Pattern detected: Design tasks taking 30% longer than estimated
```

**Recommendations Section:**
```
✅ RECOMMENDED ACTIONS

1. HIGH: Follow up on stalled "Website Redesign" project
2. MEDIUM: Review resource allocation - 3 users have 8+ active tasks
3. LOW: Consider adjusting time estimates for design work
```

**Quick Stats:**
- Tasks completed this week: 23
- Projects at risk: 3
- Average health score: 7.5/10
- Checklists completed: 18/22

**Footer:**
- "View Full Dashboard" button (links to AI PM dashboard)
- "Adjust Email Preferences" link
- Unsubscribe option (per user)

**Technical Requirements:**
- Laravel Mail class: `AiProjectManagerWeeklyReport`
- Queued job: `GenerateWeeklyAiReport`
- Scheduled in `app/Console/Kernel.php`
- HTML email template with responsive design
- Plain text alternative for email clients
- Track email opens and link clicks (analytics)
- Retry logic if OpenAI API fails during report generation

#### 3.2 On-Demand Email Reports
**Priority:** P2 (Medium)

**Description:**  
Admin can trigger an AI analysis email for a specific project or all projects anytime.

**Trigger:**
- Button on AI PM dashboard: "Email This Report"
- Button on project detail page: "Send AI Analysis"

**Content:**
- Similar structure to weekly report but focused on specific project(s)
- Generated in real-time with current data

#### 3.3 Critical Alert Emails
**Priority:** P1 (High)

**Description:**  
Immediate email alerts when critical issues are detected between weekly reports.

**Triggers:**
- High-priority task becomes overdue
- Project stalls for 7+ days
- Multiple tasks on same project are blocked
- Approaching deadline with low completion (<30%)

**Throttling:**
- Maximum 1 critical alert email per day per project
- Batch multiple critical issues into single email if detected within 1 hour

**Subject:** `🚨 Critical: {Project Name} Needs Immediate Attention`

---

#### 3.4 Automated Nudges to Team Members **NEW**
**Priority:** P1 (High)

**Description:**  
Automated reminder emails sent directly to team members (not just admin) for overdue or approaching tasks. Acts as a gentle accountability mechanism.

**Nudge Types:**

| Nudge Type | Trigger | Recipient | Frequency | Tone |
|------------|---------|-----------|-----------|------|
| **Overdue Task Reminder** | Task 1+ days overdue | Task assignee(s) | Daily (max 1 per day) | Professional, direct |
| **Upcoming Deadline** | Task due in 24-48 hours | Task assignee(s) | Once | Friendly reminder |
| **Checklist Incomplete** | Checklist <50% complete, deadline in 3 days | Task assignee(s) | Once | Helpful nudge |
| **Unopened Task Alert** | Task assigned 3+ days ago, never viewed | Task assignee(s) | Once | Gentle prompt |
| **Blocker Follow-Up** | Blocker keyword detected 3+ days ago, no update | Task assignee(s) + creator | Once | Supportive |

**Email Template Examples:**

**Overdue Task Reminder:**
```
Subject: Reminder: Your VendorConnect Task "{Task Title}" is Overdue

Hi {First Name},

This is a friendly reminder that your task is currently overdue:

📋 Task: {Task Title}
📁 Project: {Project Name}
📅 Due Date: {End Date} (overdue by {X} days)
🔗 View Task: {Task URL}

If you're facing any blockers or need support, please update the task or reach out to your project manager.

Thanks,
VendorConnect AI Assistant
```

**Upcoming Deadline:**
```
Subject: Reminder: Task "{Task Title}" Due Tomorrow

Hi {First Name},

Quick heads up - your task is due soon:

📋 Task: {Task Title}
📁 Project: {Project Name}
📅 Due Date: {End Date} (in {X} hours)
✅ Checklist: {Y}% complete
🔗 View Task: {Task URL}

You're doing great! Just a reminder to wrap this up before the deadline.

Thanks,
VendorConnect AI Assistant
```

**Unopened Task Alert:**
```
Subject: Action Needed: Task "{Task Title}" Awaiting Your Attention

Hi {First Name},

You were assigned a task {X} days ago that hasn't been opened yet:

📋 Task: {Task Title}
📁 Project: {Project Name}
📅 Due Date: {End Date}
🔗 View Task: {Task URL}

Please review the task details and let us know if you have questions or concerns.

Thanks,
VendorConnect AI Assistant
```

**Blocker Follow-Up:**
```
Subject: Follow-up: Can We Help with "{Task Title}"?

Hi {First Name},

We noticed your task has been flagged as blocked for {X} days:

📋 Task: {Task Title}
📁 Project: {Project Name}
🚧 Blocker: {Detected keyword/phrase}
🔗 View Task: {Task URL}

Is there anything we can do to help unblock this? Please update the task or reach out if you need support.

Thanks,
VendorConnect AI Assistant
```

**Configuration Settings:**

Admins can configure:
- **Enable/Disable Nudges** - Global on/off switch
- **Nudge Frequency** - How often to send (daily, every 2 days, weekly)
- **Exclude Users** - Specific users who don't want automated reminders
- **Exclude Projects** - Projects where nudges shouldn't be sent
- **Overdue Threshold** - How many days overdue before sending reminder
- **Tone Preference** - Professional / Friendly / Direct
- **Include Admin in CC** - Whether to CC admin on team member nudges

**Opt-Out Mechanism:**

Users can opt-out of nudges:
- Click "Unsubscribe from task reminders" in email footer
- Update notification preferences in user settings
- Admins can still see all nudges that would have been sent (in dashboard)

**Tracking & Analytics:**

Track nudge effectiveness:
- Nudges sent per user per week
- Task completion rate after nudge (did it help?)
- Nudge-to-action time (how long after nudge was task completed?)
- Opt-out rate
- Bounce/undeliverable rate

**Database Schema Addition:**

```sql
ALTER TABLE notifications ADD COLUMN nudge_type VARCHAR(50) NULL;
ALTER TABLE notifications ADD COLUMN nudge_sent_to_user_id BIGINT UNSIGNED NULL;
ALTER TABLE notifications ADD COLUMN parent_issue_id BIGINT UNSIGNED NULL;

CREATE INDEX idx_nudge_tracking ON notifications(nudge_type, nudge_sent_to_user_id, created_at);
```

**Implementation Notes:**

- Nudges are generated by AI analysis engine when issues detected
- Queued for delivery (don't send immediately - batch by user)
- Check user preferences before sending
- Log all nudges (sent or suppressed) for reporting
- Admin can preview nudges before they're sent (optional approval workflow)

**Integration with Phase 2 Follow-Up Emails:**

Nudges are **automatic**, while Phase 2 follow-up emails are **admin-initiated**:
- **Nudges**: System sends directly to team members (autopilot)
- **Follow-Up Emails**: Admin selects issues, customizes message, then sends

Both use same tracking infrastructure but serve different purposes.

---

### 4. AI Configuration & Settings

#### 4.1 Admin Settings Panel
**Priority:** P1 (High)

**Description:**  
Admin configuration interface for AI Project Manager behavior and preferences.

**Location:** `/settings/ai-project-manager` (Admin only)

**Settings:**

##### A. Analysis Schedule
- **Weekly Report Day**: Dropdown (Monday - Sunday)
- **Report Time**: Time picker (default 8:00 AM)
- **Timezone**: Auto-detect or manual selection
- **Frequency Options**: Weekly, Bi-weekly, Monthly (future)

##### B. Analysis Scope
- **Projects to Monitor**: 
  - All active projects (default)
  - Specific projects (multi-select)
  - Projects with specific tags
  - Projects within date range
- **Task Types to Include**: Multi-select (all types default)
- **Minimum Project Size**: Only analyze projects with X+ tasks (default 3)

##### C. Issue Detection Thresholds
- **Stale Project Duration**: Slider (5-14 days, default 7)
- **Low Checklist Threshold**: Slider (20-50%, default 30%)
- **Blocked Task Duration**: Slider (2-7 days, default 3)
- **Critical Priority Boost**: Toggle (escalate priority tasks to critical)

##### D. AI Behavior
- **AI Model**: Dropdown (GPT-4, GPT-4-Turbo, GPT-3.5-Turbo)
- **Analysis Depth**: Radio buttons
  - Quick (focus on critical issues only)
  - Standard (balanced analysis - default)
  - Deep (comprehensive with trends)
- **Tone**: Dropdown (Professional, Casual, Direct)
- **Recommendation Style**: Dropdown (Detailed, Concise, Action-Oriented)

##### E. Privacy & Data
- **Data Anonymization**: Toggle (anonymize project/user names in AI prompts)
- **Data Retention**: Dropdown (30/60/90 days for AI analysis history)
- **OpenAI API Key**: Secure input field (encrypted storage)
- **API Usage Tracking**: Display current month usage & cost estimate

##### F. Email Preferences
- **Enable Weekly Email**: Toggle (default on)
- **Enable Critical Alerts**: Toggle (default on)
- **Email Recipients**: Multi-select (all admins default)
- **CC Additional Emails**: Text input (comma-separated)
- **Email Format**: Radio (HTML, Plain Text, Both)

##### G. Dashboard Display
- **Show on Main Dashboard**: Toggle (default on)
- **Default View**: Dropdown (Collapsed, Expanded, Full Screen)
- **Max Issues Displayed**: Number input (5-20, default 5)
- **Auto-Refresh Interval**: Dropdown (30s, 60s, 5min, Manual)

**Save Actions:**
- "Save Settings" button
- "Reset to Defaults" button
- "Test AI Analysis Now" button (runs analysis on sample data)

---

### 5. Data Models & Storage

#### 5.1 Database Schema

**⚠️ CRITICAL: Data Collection Prerequisites**  

**Current State Analysis:**
VendorConnect currently collects:
- ✅ User `last_login_at` timestamps
- ✅ Basic `activity_logs` (CRUD operations)
- ✅ Task assignment history
- ✅ Task timestamps (created_at, updated_at)

**Required Data Collection (Must Build FIRST):**

To support the enhanced AI monitoring features, the following tracking capabilities **MUST BE IMPLEMENTED** before the AI Project Manager can analyze them:

| Feature | What's Needed | Implementation Approach | Estimated Effort |
|---------|--------------|------------------------|------------------|
| **Task Engagement Tracking** | Log when users view/open tasks | New table: `task_views` (task_id, user_id, viewed_at) + Frontend tracking event | 2-3 days |
| **Task Rejection Tracking** | Track rejected tasks and reasons | New status or table: `task_rejections` (task_id, user_id, reason, rejected_at) | 2-3 days |
| **Platform Usage Metrics** | Session duration and activity time | New table: `user_sessions` (user_id, login_at, logout_at, duration_seconds) + Middleware tracking | 3-4 days |
| **Historical Project Baselines** | Aggregate average project durations | One-time script + ongoing tracking: Calculate avg duration by project type/client | 2-3 days |
| **User Task Activity** | Last activity timestamp per task | Add `task_user.last_activity_at` column + update on any task interaction | 1-2 days |
| **Comment Activity Indexing** | Queryable comment timestamps | Index existing `ch_messages.created_at` by task_id (may already exist) | 1 day |

**Total Pre-Work Estimate:** 2-3 weeks of backend development

**Implementation Priority for Data Collection:**

**Phase 0 (Pre-AI PM - Weeks 1-3):**
1. **Week 1:** Task view tracking + Comment indexing
2. **Week 2:** Task rejection tracking + User task activity timestamps
3. **Week 3:** Platform usage metrics + Historical baselines calculation

**Alternative Approach:**
- Start with **limited AI PM v1.0** using only currently available data
- Add enhanced features in **v1.1-v1.3** as new tracking becomes available

**Recommendation:**  
Begin data collection **immediately** even if AI PM isn't deployed yet. This builds historical data that makes the AI analysis more valuable from day 1.

---

##### New Tracking Tables (Phase 0 - Must Build First)

**Table: `task_views`** (Track when users view tasks)
```sql
CREATE TABLE task_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    view_duration_seconds INT UNSIGNED NULL,  -- Optional: how long they viewed
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_user (task_id, user_id),
    INDEX idx_viewed_at (viewed_at),
    INDEX idx_user_views (user_id, viewed_at)
);
```

**Table: `task_rejections`** (Track task rejections)
```sql
CREATE TABLE task_rejections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    reason VARCHAR(255) NULL,
    rejection_note TEXT NULL,
    rejected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_rejected (task_id, rejected_at),
    INDEX idx_user_rejections (user_id, rejected_at)
);
```

**Table: `user_sessions`** (Track login sessions and duration)
```sql
CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    login_at TIMESTAMP NOT NULL,
    logout_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    duration_seconds INT UNSIGNED NULL,  -- Calculated on logout
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_sessions (user_id, login_at),
    INDEX idx_active_sessions (logout_at),
    INDEX idx_activity (last_activity_at)
);
```

**Table: `project_metrics_baseline`** (Historical project averages)
```sql
CREATE TABLE project_metrics_baseline (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_type VARCHAR(100) NULL,  -- If you categorize projects
    client_id BIGINT UNSIGNED NULL,
    metric_name VARCHAR(100) NOT NULL,  -- 'avg_duration_days', 'avg_task_count', etc.
    metric_value DECIMAL(10,2) NOT NULL,
    sample_size INT UNSIGNED NOT NULL,  -- How many projects in average
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_metric_lookup (project_type, metric_name)
);
```

**Column Addition: `task_user` table** (Add last activity tracking)
```sql
-- Add to existing task_user pivot table
ALTER TABLE task_user 
ADD COLUMN last_activity_at TIMESTAMP NULL,
ADD INDEX idx_last_activity (last_activity_at);
```

---

##### Table: `ai_project_analyses`
```sql
CREATE TABLE ai_project_analyses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,  -- NULL for workspace-wide analysis
    analysis_type ENUM('weekly_report', 'on_demand', 'scheduled', 'critical_alert'),
    health_score DECIMAL(3,1) DEFAULT NULL,  -- 0.0 to 10.0
    overall_status ENUM('on_track', 'at_risk', 'behind', 'critical'),
    ai_summary TEXT,  -- AI-generated executive summary
    ai_recommendations JSON,  -- Array of recommendation objects
    detected_issues JSON,  -- Array of issue objects with severity
    metrics_snapshot JSON,  -- Raw metrics at time of analysis
    prompt_used TEXT,  -- Prompt sent to OpenAI (for debugging)
    ai_response_raw TEXT,  -- Full OpenAI response
    tokens_used INT UNSIGNED,  -- API token count
    analysis_duration_ms INT UNSIGNED,  -- Processing time
    analyzed_by_user_id BIGINT UNSIGNED NULL,  -- If manually triggered
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (analyzed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project_created (project_id, created_at),
    INDEX idx_analysis_type_created (analysis_type, created_at)
);
```

##### Table: `ai_project_issues`
```sql
CREATE TABLE ai_project_issues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    analysis_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    task_id BIGINT UNSIGNED NULL,  -- Specific task if applicable
    issue_type VARCHAR(100) NOT NULL,  -- stale_project, overdue_task, etc.
    severity ENUM('low', 'medium', 'high', 'critical'),
    title VARCHAR(255) NOT NULL,  -- Brief issue title
    description TEXT,  -- AI-generated issue description
    recommendation TEXT,  -- AI-generated recommendation
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by_user_id BIGINT UNSIGNED NULL,
    resolution_note TEXT NULL,
    acknowledged_at TIMESTAMP NULL,
    acknowledged_by_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (analysis_id) REFERENCES ai_project_analyses(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (acknowledged_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_project_severity (project_id, severity),
    INDEX idx_unresolved (resolved_at),
    INDEX idx_issue_type (issue_type)
);
```

##### Table: `ai_pm_settings`
```sql
CREATE TABLE ai_pm_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json'),
    description VARCHAR(255),
    updated_by_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

##### Table: `ai_pm_email_logs`
```sql
CREATE TABLE ai_pm_email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    analysis_id BIGINT UNSIGNED,
    recipient_user_id BIGINT UNSIGNED NOT NULL,
    email_type ENUM('weekly_report', 'on_demand', 'critical_alert'),
    subject VARCHAR(255),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    status ENUM('queued', 'sent', 'failed'),
    error_message TEXT NULL,
    FOREIGN KEY (analysis_id) REFERENCES ai_project_analyses(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipient_sent (recipient_user_id, sent_at)
);
```

##### Table Updates: Add to existing `notifications` table
```sql
-- Add new notification types for AI PM
ALTER TABLE notifications
MODIFY COLUMN type VARCHAR(100);

-- New types:
-- 'ai_weekly_report'
-- 'ai_critical_alert'
-- 'ai_issue_detected'
```

#### 5.2 Model Relationships
- `AiProjectAnalysis` belongs to `Project`
- `AiProjectIssue` belongs to `AiProjectAnalysis` and `Project`
- `AiProjectIssue` optionally belongs to `Task`
- `User` has many `AiProjectAnalyses` (analyzed_by)
- `User` has many `AiPmEmailLogs` (recipient)

---

### 6. API Endpoints

#### 6.1 Dashboard Endpoints

**GET** `/api/ai-project-manager/dashboard`  
**Auth:** Admin, Sub-Admin  
**Description:** Get dashboard overview data

**Response:**
```json
{
  "success": true,
  "data": {
    "executive_summary": {
      "health_score": 7.5,
      "projects_monitored": 12,
      "issues_by_severity": {
        "critical": 3,
        "high": 2,
        "medium": 5,
        "low": 1
      },
      "top_recommendation": "Follow up on stalled 'Website Redesign' project",
      "last_analysis": "2025-09-30T08:00:00Z"
    },
    "critical_issues": [
      {
        "id": 1,
        "project_id": 5,
        "project_name": "Video Production - Client ABC",
        "issue_type": "overdue_tasks",
        "severity": "critical",
        "title": "3 tasks overdue, deadline in 2 days",
        "description": "AI-generated description...",
        "recommendation": "Reassign overdue tasks to Sarah M.",
        "detected_at": "2025-09-29T14:30:00Z"
      }
    ],
    "project_health_overview": [
      {
        "project_id": 5,
        "project_name": "Video Production",
        "health_score": 4.2,
        "status": "at_risk",
        "tasks_complete": "8/15",
        "checklist_complete": 45,
        "next_deadline": "2025-10-02",
        "issues_count": 3
      }
    ],
    "trends": {
      "completion_velocity_change": -15,
      "health_score_trend": "declining",
      "common_blockers": ["waiting_on_client", "resource_constraint"]
    }
  }
}
```

---

**GET** `/api/ai-project-manager/project/{projectId}`  
**Auth:** Admin, Sub-Admin  
**Description:** Get detailed AI analysis for specific project

**Response:**
```json
{
  "success": true,
  "data": {
    "project_id": 5,
    "health_score": 4.2,
    "status": "at_risk",
    "ai_summary": "This project is currently at risk...",
    "recommendations": [
      "Reassign overdue tasks to team members with capacity",
      "Schedule check-in meeting with client",
      "Consider extending deadline by 3 days"
    ],
    "issues": [...],
    "task_breakdown": [...],
    "checklist_analysis": {...},
    "historical_health": [...]
  }
}
```

---

**POST** `/api/ai-project-manager/analyze`  
**Auth:** Admin, Sub-Admin  
**Description:** Trigger on-demand AI analysis

**Request Body:**
```json
{
  "scope": "workspace",  // or "project"
  "project_id": null,  // required if scope is "project"
  "depth": "standard"  // quick, standard, deep
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "analysis_id": 123,
    "status": "processing",  // or "completed"
    "estimated_time_seconds": 15
  }
}
```

---

#### 6.2 Issue Management Endpoints

**PATCH** `/api/ai-project-manager/issues/{issueId}/acknowledge`  
**Auth:** Admin, Sub-Admin  
**Description:** Mark issue as acknowledged

**Response:**
```json
{
  "success": true,
  "data": {
    "issue_id": 456,
    "acknowledged": true,
    "acknowledged_at": "2025-09-30T10:30:00Z"
  }
}
```

---

**PATCH** `/api/ai-project-manager/issues/{issueId}/resolve`  
**Auth:** Admin, Sub-Admin  
**Description:** Mark issue as resolved with optional note

**Request Body:**
```json
{
  "resolution_note": "Reassigned tasks and extended deadline"
}
```

---

#### 6.3 Settings Endpoints

**GET** `/api/ai-project-manager/settings`  
**Auth:** Admin  
**Description:** Get all AI PM settings

---

**PUT** `/api/ai-project-manager/settings`  
**Auth:** Admin  
**Description:** Update AI PM settings

**Request Body:**
```json
{
  "weekly_report_day": "monday",
  "report_time": "08:00",
  "ai_model": "gpt-4-turbo",
  "analysis_depth": "standard",
  "enable_critical_alerts": true
}
```

---

#### 6.4 Email Endpoints

**POST** `/api/ai-project-manager/email/send`  
**Auth:** Admin, Sub-Admin  
**Description:** Send on-demand AI report email

**Request Body:**
```json
{
  "type": "on_demand",
  "project_id": null,  // null for workspace-wide
  "recipients": ["admin@example.com"],
  "include_recommendations": true
}
```

---

### 7. Background Jobs & Scheduled Tasks

#### 7.1 Scheduled Jobs (Laravel Task Scheduler)

**Weekly Report Generation**  
- **Class:** `App\Console\Commands\GenerateAiWeeklyReport`
- **Schedule:** Weekly (configurable day/time)
- **Process:**
  1. Query all active projects
  2. Gather metrics for each project
  3. Call OpenAI API for analysis
  4. Store results in `ai_project_analyses`
  5. Detect issues and store in `ai_project_issues`
  6. Queue email jobs for admins

**Continuous Health Monitoring**  
- **Class:** `App\Console\Commands\MonitorProjectHealth`
- **Schedule:** Every 6 hours
- **Process:**
  1. Check projects for threshold violations
  2. Detect critical issues
  3. Generate alerts if needed
  4. Queue critical alert emails

**API Usage Tracking**  
- **Class:** `App\Console\Commands\TrackAiApiUsage`
- **Schedule:** Daily at midnight
- **Process:**
  1. Sum token usage for the day
  2. Calculate cost estimate
  3. Store in settings/logs
  4. Alert if approaching budget limits

#### 7.2 Queued Jobs (Laravel Queue)

**GenerateAiAnalysisJob**
- **Trigger:** Scheduled or on-demand
- **Timeout:** 120 seconds
- **Retry:** 3 times
- **Process:**
  1. Collect project data
  2. Format prompt for OpenAI
  3. Call OpenAI API
  4. Parse response
  5. Store analysis
  6. Dispatch notification jobs

**SendAiReportEmailJob**
- **Trigger:** After analysis generation
- **Priority:** Medium
- **Process:**
  1. Fetch analysis data
  2. Render email template
  3. Send via configured mail driver
  4. Log email sent status

**SendCriticalAlertEmailJob**
- **Trigger:** Critical issue detected
- **Priority:** High (expedited queue)
- **Process:**
  1. Format alert email
  2. Send immediately
  3. Log alert

---

### 8. UI/UX Design Guidelines

#### 8.1 Visual Design

**Color Scheme:**
- **Health Score Indicators:**
  - 8-10: Green (#10B981) - Excellent health
  - 6-7.9: Yellow (#F59E0B) - Good, minor attention needed
  - 4-5.9: Orange (#F97316) - At risk, needs attention
  - 0-3.9: Red (#EF4444) - Critical, immediate action required

**Typography:**
- Headers: Bold, clear hierarchy
- AI-generated text: Slightly softer font weight to distinguish from data
- Issue titles: Medium weight, prominent

**Icons:**
- Health score: Heart icon with pulse animation
- Critical issues: Alert triangle
- Recommendations: Lightbulb
- Trends: Chart/graph icons
- Email: Envelope icon

**Layout:**
- Card-based design for modularity
- Ample white space for readability
- Responsive grid (mobile-first)
- Sticky header for AI PM section when scrolling

#### 8.2 Interaction Design

**Loading States:**
- Skeleton loaders for AI content while processing
- Progress indicator for on-demand analysis
- Pulsing animation for "AI is thinking"

**Empty States:**
- Positive messaging when no issues detected
- Illustrations to reduce anxiety
- Clear next actions

**Error States:**
- Graceful degradation if AI fails
- Show raw metrics as fallback
- "Try Again" button with countdown timer

**Micro-interactions:**
- Smooth transitions between views
- Hover effects on interactive elements
- Success animations when marking issues resolved
- Confetti animation when all issues cleared

#### 8.3 Accessibility

- WCAG 2.1 Level AA compliance
- Keyboard navigation support
- Screen reader friendly labels
- Sufficient color contrast (4.5:1 minimum)
- Focus indicators on interactive elements
- Alt text for all images/icons

---

### 9. Security & Privacy

#### 9.1 Access Control
- **Role-Based Access:** Only Admin and Sub-Admin roles can access AI PM features
- **API Authentication:** All endpoints require valid Sanctum bearer token
- **Authorization Checks:** Verify user role on every request
- **Rate Limiting:** 60 requests per minute per user for AI endpoints

#### 9.2 Data Privacy
- **OpenAI Data Handling:**
  - Optional anonymization of project/user names
  - No personally identifiable information (PII) sent unless explicitly enabled
  - Data retention: OpenAI API stores data per their policy (check latest terms)
- **GDPR Compliance:**
  - Admin can export AI analysis data
  - Admin can delete AI analysis history
  - Email opt-out mechanism
- **Data Encryption:**
  - OpenAI API keys encrypted at rest (Laravel encryption)
  - Sensitive settings encrypted
  - HTTPS/TLS for all API communication

#### 9.3 API Security
- **OpenAI API Key:**
  - Stored in `.env` file (never in version control)
  - Rotatable via settings panel
  - Separate keys for dev/staging/production
- **Rate Limiting:**
  - Prevent abuse of AI analysis endpoints
  - Queue-based throttling for background jobs
- **Input Validation:**
  - Sanitize all data before sending to OpenAI
  - Prevent prompt injection attacks
  - Validate JSON responses from OpenAI

---

### 10. Performance & Scalability

#### 10.1 Performance Targets
- **Dashboard Load Time:** < 2 seconds (initial load)
- **AI Analysis Generation:** < 30 seconds for standard depth
- **Email Delivery:** < 5 minutes after analysis completion
- **API Response Time:** < 500ms for dashboard endpoint

#### 10.2 Optimization Strategies
- **Caching:**
  - Cache dashboard data for 5 minutes (Redis/Memcached)
  - Cache project metrics for 15 minutes
  - Cache AI analysis results until next scheduled run
- **Database Indexing:**
  - Index on frequently queried fields (see schema)
  - Composite indexes for common query patterns
- **Query Optimization:**
  - Eager loading for relationships
  - Pagination for large result sets
  - Database views for complex aggregations
- **Background Processing:**
  - All AI analysis runs asynchronously
  - Queue prioritization (critical alerts first)
  - Horizontal scaling with multiple queue workers

#### 10.3 Scalability Considerations
- **Large Workspace Support:**
  - Batch processing for 100+ projects
  - Chunked API calls to OpenAI (multiple smaller requests)
  - Progressive loading in UI
- **OpenAI API Limits:**
  - Respect rate limits (configurable per model)
  - Implement exponential backoff retry
  - Fallback to cached analysis if API unavailable
- **Cost Management:**
  - Track token usage and cost
  - Admin alerts if monthly budget exceeded
  - Option to pause AI analysis if over budget

---

### 11. Testing Requirements

#### 11.1 Unit Tests
- Test AI prompt generation logic
- Test issue detection rules and thresholds
- Test metric calculation functions
- Test email content generation
- Test settings validation

#### 11.2 Integration Tests
- Test OpenAI API integration (with mocks)
- Test database queries and relationships
- Test queue job processing
- Test email sending (with mocks)
- Test API endpoint responses

#### 11.3 End-to-End Tests
- Test full weekly report generation flow
- Test on-demand analysis trigger
- Test dashboard data display
- Test settings update and persistence
- Test critical alert email delivery

#### 11.4 Manual Testing Checklist
- [ ] Verify AI generates sensible insights for various project states
- [ ] Confirm email formatting looks good in multiple clients (Gmail, Outlook, Apple Mail)
- [ ] Test with large workspace (50+ projects)
- [ ] Test error handling when OpenAI API is down
- [ ] Test timezone handling for scheduled reports
- [ ] Verify role-based access restrictions
- [ ] Test mobile responsive design

---

### 12. Success Criteria & KPIs

#### 12.1 Launch Criteria (Must Have)
- [ ] AI analysis runs successfully on schedule
- [ ] Dashboard displays health scores and issues accurately
- [ ] Weekly email reports sent reliably
- [ ] Settings panel functional and persists changes
- [ ] Critical alerts trigger appropriately
- [ ] No security vulnerabilities in code review
- [ ] Performance targets met under load testing
- [ ] Documentation complete (user guide, admin guide, API docs)

#### 12.2 Key Performance Indicators (Post-Launch)

**Adoption Metrics:**
- % of admins who view AI PM dashboard weekly
- Average time spent on AI PM dashboard per session
- Number of on-demand analyses triggered per week

**Engagement Metrics:**
- Weekly email open rate
- Weekly email click-through rate
- Issues acknowledged/resolved per week
- Settings customization rate (% admins who change defaults)

**Impact Metrics:**
- Average time to detect issues (AI vs. manual baseline)
- % reduction in overdue tasks after 3 months
- % improvement in on-time project completion
- Admin reported time savings (via survey)

**System Health:**
- AI API uptime and success rate
- Average analysis generation time
- Token usage and cost per month
- Email delivery success rate

**Quality Metrics:**
- AI insight relevance score (admin feedback survey)
- False positive rate for issue detection
- Admin satisfaction score (NPS)

---

### 13. Implementation Phases

#### Phase 0: Data Collection Infrastructure (Weeks 1-3) ⚠️ **PREREQUISITE**

**Goal:** Build tracking infrastructure to collect data needed for AI analysis

**Deliverables:**
- `task_views` table + frontend tracking events
- `task_rejections` table + rejection workflow UI
- `user_sessions` table + session tracking middleware
- `project_metrics_baseline` table + historical data aggregation script
- `task_user.last_activity_at` column + update logic
- Comment activity indexing verification
- Database migrations for all new tables
- Backend API endpoints for tracking events
- Frontend instrumentation for task views

**Tasks Breakdown:**

**Week 1: Task Engagement Tracking**
- Create `task_views` migration
- Add frontend event when user opens task detail page
- Backend API endpoint to log task views
- Index existing comment timestamps

**Week 2: Task Rejection & Activity Tracking**
- Create `task_rejections` table
- Add "Reject Task" button/workflow in UI (optional)
- Update `task_user` table with `last_activity_at`
- Add middleware to update activity timestamp on any task interaction

**Week 3: Platform Usage & Historical Baselines**
- Create `user_sessions` table
- Add login/logout session tracking
- Create `project_metrics_baseline` table
- Write script to calculate historical averages from existing data
- Run initial baseline calculation

**Success Criteria:**
- All tracking tables created and collecting data
- Frontend successfully logs task views
- Session tracking captures login/logout times
- Historical baselines calculated for at least 10 completed projects
- No performance degradation from tracking overhead

**⚠️ CRITICAL:** This phase should ideally run for **2-4 weeks before starting Phase 1** to accumulate meaningful historical data for AI analysis. However, development can proceed in parallel.

---

#### Phase 1: MVP (Weeks 4-8)
**Goal:** Core AI analysis engine and basic dashboard display

**Deliverables:**
- Database schema and migrations
- OpenAI API integration service
- Basic AI prompt engineering
- Issue detection rules engine
- Scheduled job for weekly analysis
- Simple dashboard widget showing health scores and top issues
- Backend API endpoints for dashboard data

**Success Criteria:**
- AI generates coherent analysis for sample projects
- Dashboard displays data without errors
- Scheduled job runs reliably weekly

---

#### Phase 2: Email Reports (Weeks 5-6)
**Goal:** Automated email reporting system

**Deliverables:**
- Email template design (HTML/plain text)
- Weekly report email generation
- Email scheduling and queue
- Email tracking (opens/clicks)
- User notification preferences
- Critical alert email system

**Success Criteria:**
- Emails sent on schedule to all admins
- Emails render correctly in major clients
- Critical alerts trigger appropriately

---

#### Phase 3: Enhanced Dashboard (Weeks 7-8)
**Goal:** Rich dashboard experience with full insights

**Deliverables:**
- Expanded dashboard UI components
- Detailed project analysis view
- Issue acknowledgement/resolution workflow
- Trends and analytics charts
- On-demand analysis trigger
- Export functionality (PDF reports)

**Success Criteria:**
- Dashboard is visually polished and responsive
- Admins can drill down into project details
- On-demand analysis completes in <30s

---

#### Phase 4: Settings & Configuration (Week 9)
**Goal:** Admin control over AI behavior and preferences

**Deliverables:**
- Settings panel UI
- Settings API endpoints
- Configurable thresholds and schedules
- AI model and behavior options
- Privacy and data controls
- Settings persistence and validation

**Success Criteria:**
- All settings save and apply correctly
- Admin can customize AI PM to their needs

---

#### Phase 5: Polish & Optimization (Week 10)
**Goal:** Performance optimization and user experience refinement

**Deliverables:**
- Performance optimizations (caching, indexing)
- Error handling improvements
- Loading state refinements
- Mobile responsive design polish
- Accessibility audit and fixes
- Documentation (user guides, admin docs)

**Success Criteria:**
- Performance targets met
- All edge cases handled gracefully
- Documentation complete

---

#### Phase 6: Beta Testing (Weeks 11-12)
**Goal:** Real-world testing and feedback gathering

**Deliverables:**
- Beta release to select admins
- Feedback collection mechanism
- Bug fixes and iterations
- AI prompt tuning based on feedback
- User satisfaction survey

**Success Criteria:**
- Beta users actively use the feature
- Major bugs identified and fixed
- Positive feedback on AI insight quality

---

#### Phase 7: Production Launch (Week 13)
**Goal:** Full rollout to all VendorConnect users

**Deliverables:**
- Production deployment
- Announcement and onboarding materials
- Launch email to admins
- Feature spotlight in dashboard
- Monitoring and alerting setup

**Success Criteria:**
- Smooth deployment with no major issues
- Admins aware of new feature
- Monitoring shows healthy metrics

---

#### Phase 8: AI Follow-Up Email Generator (Weeks 14-17) - PHASE 2
**Goal:** Add AI-powered follow-up email capability

**Deliverables:**
- Issue selection interface with checkboxes
- Recipient selection logic
- AI email generation API integration
- Email editor with rich text support
- Tone customization options
- Send and scheduling functionality
- Email tracking (opens, clicks, responses)
- Integration with existing AI PM dashboard
- Documentation updates

**Success Criteria:**
- Admins can generate emails from issues in <30 seconds
- AI-generated emails require minimal editing (<30% edit rate)
- Email tracking shows 60%+ response rate
- Positive feedback from beta testers (8+/10 satisfaction)

---

### 14. Dependencies & Prerequisites

#### 14.1 Technical Dependencies
- **OpenAI API Account:**
  - Organization account with billing enabled
  - API key with GPT-4 access
  - Estimated monthly budget allocation ($200-500 depending on usage)
- **Laravel/PHP:**
  - Laravel 9+ (check current VendorConnect version)
  - PHP 8.1+
  - Laravel Queue configured (Redis, database, or SQS)
  - Laravel Scheduler (cron job) configured
- **Database:**
  - MySQL 8.0+
  - Space for AI analysis data (~10GB estimated first year)
- **Frontend:**
  - Next.js 14 (current VendorConnect version)
  - React 18+
  - API client library configured
- **Email:**
  - Laravel Mail configured (SMTP, SendGrid, SES, etc.)
  - Email templates support (Blade, Maizzle, or similar)

#### 14.2 External Services
- OpenAI API (GPT-4/GPT-4-Turbo)
- Email delivery service (existing VendorConnect setup)
- Optional: Email tracking service (SendGrid, Postmark)

#### 14.3 Internal Prerequisites
- Existing VendorConnect features:
  - Projects and Tasks models
  - User roles (Admin, Sub-Admin)
  - Dashboard framework
  - Notification system
  - Settings infrastructure

---

### 15. Risks & Mitigation

#### 15.1 Technical Risks

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|---------|---------------------|
| **OpenAI API outage** | Medium | High | Implement caching, graceful degradation to raw metrics, retry logic |
| **High API costs** | Medium | Medium | Token usage tracking, budget alerts, rate limiting, caching |
| **Poor AI insight quality** | Medium | High | Extensive prompt engineering, feedback loop, human review in beta |
| **Performance issues with large datasets** | Medium | Medium | Query optimization, pagination, background processing, caching |
| **Email delivery failures** | Low | Medium | Queue retry logic, monitor delivery rates, fallback to in-app notifications |

#### 15.2 Product Risks

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|---------|---------------------|
| **Low adoption by admins** | Medium | High | User research, intuitive UX, onboarding guide, value demonstration |
| **AI recommendations not actionable** | Medium | High | Prompt tuning, beta testing, feedback incorporation |
| **False positive overload** | Medium | Medium | Threshold tuning, severity calibration, user feedback loop |
| **Privacy concerns with OpenAI** | Low | Medium | Anonymization options, transparency, data handling documentation |
| **Feature complexity overwhelming** | Low | Medium | Progressive disclosure, simple default settings, optional advanced features |

#### 15.3 Business Risks

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|---------|---------------------|
| **Insufficient ROI** | Low | Medium | Track time savings, measure impact on project success, iterate based on data |
| **Competitive offerings emerge** | Medium | Low | Focus on integration with VendorConnect, unique insights, continuous improvement |
| **OpenAI policy changes** | Low | High | Monitor OpenAI terms, have fallback AI providers scoped (Anthropic, local models) |

---

### 16. Future Enhancements (Post-v1.0)

#### 16.1 Phase 2 Features (Roadmap)

**AI-Powered Follow-Up Email Generator** ⭐ **HIGH PRIORITY**
- Admin sees list of detected issues with checkboxes
- Select issues to include in follow-up email
- AI generates contextual, professional follow-up email for each issue
- Admin can edit AI-generated email content before sending
- Multi-user selection (send to specific users, project teams, or all)
- Email templates adapt based on issue severity and recipient role
- Track email responses and follow-up actions
- See detailed specification in Section 16.4 below

**Conversational AI Interface ("Ask AI"):**
- Chat interface for natural language queries
- "Ask AI anything about my projects"
- Real-time Q&A instead of static reports
- Context-aware follow-up questions

**Predictive Analytics:**
- Predict project completion dates based on velocity
- Forecast resource needs
- Identify high-risk projects before issues manifest

**Automated Actions:**
- AI-suggested task reassignments (with admin approval)
- Auto-generation of follow-up tasks
- Smart resource reallocation recommendations

**Team Performance Insights:**
- Individual user productivity analysis
- Team collaboration patterns
- Skill gap identification
- Workload balance recommendations

**Client-Specific Analysis:**
- Client health scores
- Client engagement patterns
- At-risk client identification

**Advanced Visualizations:**
- Interactive project timeline with AI annotations
- Network graphs of task dependencies
- Heat maps of project activity

**Mobile App:**
- Native iOS/Android app with AI PM dashboard
- Push notifications for critical issues
- Quick-view summaries

**Voice Briefings:**
- Audio summary of weekly report
- Alexa/Siri integration
- "Listen to my project status"

**Integration Ecosystem:**
- Slack bot for AI insights
- Microsoft Teams integration
- API webhooks for third-party tools

#### 16.2 AI Model Improvements

- **Fine-tuning:** Train custom model on VendorConnect-specific data
- **Multi-model approach:** Use different models for different analysis types
- **Local LLM option:** For privacy-sensitive customers
- **Sentiment analysis:** Analyze comment tone and team morale

#### 16.3 Enterprise Features

- **Multi-workspace support:** Cross-workspace analytics for enterprise customers
- **Custom reporting:** Admin-defined report templates
- **White-label:** Rebrand AI PM for specific customers
- **Advanced permissions:** Granular control over who sees what
- **Audit logs:** Track all AI analysis and actions

---

#### 16.4 Detailed Specification: AI-Powered Follow-Up Email Generator (Phase 2)

**Priority:** P0 for Phase 2  
**Status:** Future Enhancement  
**Estimated Effort:** 3-4 weeks

##### Overview
The AI-Powered Follow-Up Email Generator enables admins to quickly compose and send professional follow-up emails to team members about detected issues. Instead of manually writing emails for each problem, admins can select issues from a list, have AI generate appropriate email content, customize it, and send to relevant users.

##### User Flow

**Step 1: Issue Selection Interface**
- Admin views AI PM dashboard with detected issues
- Each issue has a checkbox next to it
- Issues are grouped by project and severity
- "Compose Follow-Up Email" button appears when 1+ issues selected
- Bulk select options: "Select All Critical", "Select All for Project X"

**Step 2: Recipient Selection**
- Modal opens showing selected issues
- For each issue, suggest relevant recipients:
  - Task assignee(s)
  - Project team members
  - Task creator
  - Specific user if issue is user-related (e.g., "John hasn't logged in")
- Admin can add/remove recipients
- Option to send as individual emails or combined digest
- CC and BCC fields available

**Step 3: AI Email Generation**
- Click "Generate Email Content" button
- AI analyzes selected issues and generates email for each recipient
- Email includes:
  - Personalized greeting
  - Context about the issue(s)
  - Specific concerns or data points
  - Clear ask/action items
  - Professional tone adapted to severity
  - Deadline for response if applicable

**Step 4: Review & Edit Interface**
- Split-pane view:
  - Left: List of recipients and preview
  - Right: Email editor for selected recipient
- Each generated email shown in editor with:
  - Subject line (editable)
  - Email body (rich text editor)
  - Issue references (can be toggled on/off per issue)
  - Suggested action items (can be removed/added)
- AI suggestions panel showing alternative phrasings
- "Regenerate" button to get new AI-generated content
- Tone selector: Professional / Friendly / Direct / Urgent

**Step 5: Send & Track**
- Review summary: X emails to Y recipients
- "Send Now" or "Schedule Send" options
- Confirmation modal with preview
- After sending:
  - Track delivery status
  - Track opens and clicks
  - Track responses (if reply-to is monitored email)
  - Link back to original issues
  - Reminder to follow up if no response in X days

##### UI Components

**Issue Selection Table**
```
┌─────────────────────────────────────────────────────────────┐
│ ☐ Select All Critical (3)    [Compose Follow-Up Email]     │
├─────────────────────────────────────────────────────────────┤
│ 🔴 Critical Issues                                          │
├─┬───────────────────────────────────────────────────────────┤
│☐│ Video Production - Client ABC                            │
│ │ 📌 3 tasks overdue, deadline in 2 days                   │
│ │ 👤 Assigned to: Sarah M., John D.                        │
│ │ 💡 AI Recommendation: Reassign or extend deadline        │
├─┼───────────────────────────────────────────────────────────┤
│☑│ Website Redesign - Client XYZ                            │
│ │ 📌 Project stalled - no activity in 9 days               │
│ │ 👤 Assigned to: Mike T.                                  │
│ │ 💡 AI Recommendation: Check in with team                 │
└─┴───────────────────────────────────────────────────────────┘
```

**Email Generation Modal**
```
┌─────────────────────────────────────────────────────────────┐
│ Compose Follow-Up Email                              [✕]    │
├─────────────────────────────────────────────────────────────┤
│ Step 2 of 4: Select Recipients                              │
│                                                              │
│ Selected Issues: 2                                           │
│                                                              │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Issue 1: Video Production - 3 tasks overdue          │  │
│ │ Suggested Recipients:                                 │  │
│ │ ☑ Sarah M. (Task Assignee)                           │  │
│ │ ☑ John D. (Task Assignee)                            │  │
│ │ ☐ Emily R. (Project Lead)                            │  │
│ │ [+ Add Recipient]                                     │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                              │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Issue 2: Website Redesign - Project stalled          │  │
│ │ Suggested Recipients:                                 │  │
│ │ ☑ Mike T. (Team Member)                              │  │
│ │ [+ Add Recipient]                                     │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                              │
│ Email Strategy:                                              │
│ ◉ Individual emails to each person (personalized)           │
│ ○ Combined email to all recipients (digest)                 │
│                                                              │
│         [← Back]    [Generate Email Content →]              │
└─────────────────────────────────────────────────────────────┘
```

**Email Editor Interface**
```
┌─────────────────────────────────────────────────────────────┐
│ Review & Edit Emails (3 of 3)                         [✕]   │
├──────────────────────┬──────────────────────────────────────┤
│ Recipients:          │ To: Sarah M. (sarah@example.com)     │
│                      ├──────────────────────────────────────┤
│ ☑ Sarah M.           │ Subject:                             │
│   2 issues           │ ┌──────────────────────────────────┐ │
│ ☐ John D.            │ │ Follow-up: Video Production Tasks│ │
│   2 issues           │ └──────────────────────────────────┘ │
│ ☐ Mike T.            │                                       │
│   1 issue            │ ┌──────────────────────────────────┐ │
│                      │ │ Hi Sarah,                        │ │
│ [Generate All]       │ │                                   │ │
│                      │ │ I wanted to follow up regarding  │ │
│ Tone: Professional ▼ │ │ the Video Production project for │ │
│                      │ │ Client ABC.                      │ │
│ ☐ Include issue IDs  │ │                                   │ │
│ ☐ Request reply      │ │ I've noticed a few items that    │ │
│ ☑ Suggest deadline   │ │ need attention:                  │ │
│                      │ │                                   │ │
│                      │ │ ☑ Task #127 is 3 days overdue    │ │
│                      │ │ ☑ Task #128 is 2 days overdue    │ │
│                      │ │                                   │ │
│                      │ │ The project deadline is in 2 days│ │
│                      │ │ Could you provide a status update│ │
│                      │ │ by end of day?                   │ │
│                      │ │                                   │ │
│                      │ │ Let me know if you need support. │ │
│                      │ │                                   │ │
│                      │ │ Thanks,                          │ │
│                      │ │ [Your Name]                      │ │
│                      │ └──────────────────────────────────┘ │
│                      │                                       │
│                      │ [↻ Regenerate] [Preview] [Edit ✏️]  │
│                      │                                       │
│                      │ ✨ AI Suggestions:                   │
│                      │ • Use "urgent" for higher priority   │
│                      │ • Mention team resources available   │
├──────────────────────┴──────────────────────────────────────┤
│         [← Back]    [Send All (3 emails) →]                 │
└─────────────────────────────────────────────────────────────┘
```

##### AI Email Generation Logic

**Context Provided to OpenAI:**
```
Generate a follow-up email for the following situation:

SENDER: [Admin Name], [Admin Title]
RECIPIENT: [User Name], [User Role]
RELATIONSHIP: [e.g., "Admin to Team Member", "Project Manager to Tasker"]

ISSUES TO ADDRESS:
1. Issue Type: [e.g., "Overdue Tasks"]
   Project: [Project Name]
   Details: [e.g., "Task #127 'Edit Final Cut' is 3 days overdue"]
   Deadline: [Project deadline or task deadline]
   Assignment Date: [When task was assigned]
   
2. Issue Type: [e.g., "Low Engagement"]
   Project: [Project Name]
   Details: [e.g., "Task #128 assigned 5 days ago but not opened"]

RECIPIENT CONTEXT:
- Current workload: [X active tasks]
- Recent activity: [Last login, recent completions]
- Historical performance: [e.g., "Usually completes tasks on time"]

GOAL: [e.g., "Get status update and ensure tasks are completed by deadline"]
TONE: [Professional / Friendly / Direct / Urgent based on severity]
INCLUDE: Clear action items, specific deadlines for response

Generate:
1. Email subject line
2. Professional email body (200-300 words max)
3. Clear call-to-action
4. Appropriate tone for the severity and relationship
```

**Email Templates by Severity:**

*Critical Issues (Urgent Tone):*
```
Subject: Urgent: [Project Name] - Immediate Action Required

Hi [Name],

I need to bring an urgent matter to your attention regarding [Project Name].

[Issue details with specific data points]

This is critical because [impact/deadline explanation].

**Action needed by [specific date/time]:**
- [Action item 1]
- [Action item 2]

Please reply ASAP or let me know if you need support.

Thanks,
[Admin Name]
```

*High Issues (Direct Tone):*
```
Subject: Follow-up needed: [Project Name]

Hi [Name],

I wanted to check in about [Project Name]. I've noticed:

[Issue details]

Can you please:
- [Action item 1]
- [Action item 2]
- Respond by [date] with status update

Let me know if you're facing any blockers.

Thanks,
[Admin Name]
```

*Medium Issues (Professional/Friendly Tone):*
```
Subject: Status check: [Project Name]

Hi [Name],

Hope you're doing well! I wanted to touch base about [Project Name].

[Issue details presented conversationally]

When you have a moment, could you:
- [Action item 1]
- [Action item 2]

No rush, but ideally by [date] if possible.

Let me know if you need anything!

Thanks,
[Admin Name]
```

##### Features & Capabilities

**Customization Options:**
- **Tone Selection**: Professional, Friendly, Direct, Urgent, Custom
- **Template Selection**: Use pre-defined templates or full AI generation
- **Issue Grouping**: Group similar issues in one email vs. separate emails
- **Personalization Variables**: {user_name}, {project_name}, {deadline}, {issue_count}
- **Action Item Editor**: Add, remove, or reorder requested actions
- **Deadline Selector**: Set response deadline (end of day, tomorrow, 3 days, 1 week)

**Batch Operations:**
- Generate emails for all selected issues at once
- Apply same tone to all emails
- Send all emails with one click
- Schedule all for future send time

**Smart Suggestions:**
- AI suggests optimal recipients based on issue type
- AI suggests best time to send (based on recipient timezone and activity patterns)
- AI suggests follow-up schedule if no response
- AI identifies if similar emails were sent recently (avoid spam)

**Tracking & Follow-Up:**
- Dashboard widget: "Pending Email Responses" (X emails sent, Y opened, Z responded)
- Auto-reminder if no response in configured timeframe
- Link email conversations back to original issues
- Mark issues as "Addressed" when recipient responds or completes action

##### Database Schema Additions

**Table: `ai_followup_emails`**
```sql
CREATE TABLE ai_followup_emails (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sent_by_user_id BIGINT UNSIGNED NOT NULL,
    recipient_user_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_text TEXT NOT NULL,
    body_html TEXT,
    tone ENUM('professional', 'friendly', 'direct', 'urgent', 'custom'),
    related_issues JSON,  -- Array of issue IDs included
    status ENUM('draft', 'scheduled', 'sent', 'delivered', 'opened', 'clicked', 'replied'),
    scheduled_send_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    replied_at TIMESTAMP NULL,
    response_text TEXT NULL,
    ai_generated BOOLEAN DEFAULT TRUE,
    ai_prompt TEXT,  -- Prompt used for generation
    tokens_used INT UNSIGNED,
    admin_edited BOOLEAN DEFAULT FALSE,  -- Did admin edit AI content?
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sent_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_recipient_status (recipient_user_id, status),
    INDEX idx_sent_opened (sent_at, opened_at)
);
```

**Table: `ai_followup_templates`** (Optional - for saved templates)
```sql
CREATE TABLE ai_followup_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    template_subject VARCHAR(255),
    template_body TEXT,
    issue_types JSON,  -- Which issue types this template applies to
    default_tone ENUM('professional', 'friendly', 'direct', 'urgent'),
    is_default BOOLEAN DEFAULT FALSE,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

##### API Endpoints

**POST** `/api/ai-project-manager/followup-emails/generate`  
**Request:**
```json
{
  "issue_ids": [123, 124, 125],
  "recipients": [
    {
      "user_id": 45,
      "issue_ids": [123, 124]
    },
    {
      "user_id": 67,
      "issue_ids": [125]
    }
  ],
  "tone": "professional",
  "email_strategy": "individual",  // or "combined"
  "include_issue_ids": true,
  "request_reply": true,
  "response_deadline": "2025-10-05T17:00:00Z"
}
```
**Response:**
```json
{
  "success": true,
  "data": {
    "emails": [
      {
        "recipient_user_id": 45,
        "recipient_name": "Sarah M.",
        "subject": "Follow-up: Video Production Tasks",
        "body_html": "<p>Hi Sarah,...</p>",
        "body_text": "Hi Sarah,...",
        "related_issues": [123, 124],
        "draft_id": 789
      },
      // ... more emails
    ],
    "total_generated": 2,
    "total_tokens_used": 1200
  }
}
```

---

**PUT** `/api/ai-project-manager/followup-emails/{draftId}`  
Edit generated email before sending

---

**POST** `/api/ai-project-manager/followup-emails/send`  
Send one or more generated emails

**Request:**
```json
{
  "draft_ids": [789, 790],
  "send_immediately": true,
  "scheduled_send_at": null
}
```

---

**GET** `/api/ai-project-manager/followup-emails/tracking`  
Get status of sent follow-up emails

**Response:**
```json
{
  "success": true,
  "data": {
    "pending_responses": 5,
    "emails_sent_this_week": 12,
    "response_rate": 0.65,
    "average_response_time_hours": 18,
    "emails": [
      {
        "id": 789,
        "recipient": "Sarah M.",
        "subject": "Follow-up: Video Production Tasks",
        "sent_at": "2025-09-30T10:00:00Z",
        "opened_at": "2025-09-30T11:30:00Z",
        "replied_at": null,
        "status": "opened",
        "related_issues": [123, 124]
      }
    ]
  }
}
```

##### Success Metrics for Phase 2

- **Adoption**: 70%+ of admins use follow-up email generator monthly
- **Time Savings**: 15 minutes saved per follow-up email (vs. manual writing)
- **Response Rate**: 60%+ response rate to AI-generated emails
- **Issue Resolution**: 40% faster issue resolution when follow-up emails sent
- **User Satisfaction**: Admin satisfaction score 8+/10 for email quality
- **Edit Rate**: <30% of emails require significant edits (indicates good AI quality)

##### Implementation Timeline (Phase 2)

**Week 1-2:**
- Design UI mockups for all screens
- Database schema and migrations
- Basic email generation API integration
- Issue selection interface

**Week 3:**
- Recipient selection logic
- AI email generation with multiple templates
- Email editor interface with rich text support

**Week 4:**
- Send and tracking functionality
- Email tracking integration (opens, clicks)
- Testing and polish
- Documentation

---


### 17. User Documentation

#### 17.1 Admin User Guide (Required Sections)

**Getting Started with AI Project Manager**
- What is the AI Project Manager?
- How does it work?
- How to access the dashboard
- Understanding health scores

**Reading AI Insights**
- Interpreting the executive summary
- Understanding issue severity levels
- What AI recommendations mean
- How to act on insights

**Email Reports**
- What to expect in weekly emails
- How to customize email preferences
- Understanding critical alerts
- Managing email subscriptions

**Settings & Configuration**
- Customizing analysis schedules
- Adjusting detection thresholds
- Privacy and data options
- OpenAI API key setup

**Issue Management**
- Acknowledging issues
- Resolving issues
- Tracking issue history

**FAQ**
- How accurate is the AI?
- What if I disagree with the AI?
- How much does this cost?
- How is my data used?
- Troubleshooting common issues

#### 17.2 Developer Documentation

**API Reference:**
- Complete endpoint documentation
- Request/response examples
- Authentication and authorization
- Rate limits and error codes

**Integration Guide:**
- How to extend AI PM functionality
- Custom issue detection rules
- Webhook integration
- Third-party API connections

**Database Schema:**
- Entity-relationship diagrams
- Table descriptions
- Index strategies

**AI Prompt Engineering:**
- How prompts are structured
- How to customize prompts
- Best practices for AI interaction

---

### 18. Compliance & Legal

#### 18.1 Data Processing Agreement
- Document how customer data is processed
- OpenAI data usage and retention policies
- Customer data residency (if applicable)
- Right to opt-out of AI processing

#### 18.2 Terms of Service Addendum
- AI-generated insights are advisory, not guarantees
- Disclaimer that AI may produce inaccurate recommendations
- User responsibility for final decisions
- Service availability and uptime commitments

#### 18.3 Privacy Policy Updates
- Disclose use of OpenAI API
- Explain what data is sent to OpenAI
- User control over data sharing
- Data retention policies for AI analysis

---

### 19. Budget & Resource Estimation

#### 19.1 Development Costs

**Phase 0 (Data Collection Infrastructure - 3 weeks)** ⚠️ **MUST BUILD FIRST**

| Role | Time Allocation | Estimated Hours |
|------|----------------|----------------|
| **Backend Developer (Laravel)** | 80% full-time | ~95 hours |
| **Frontend Developer (Next.js)** | 40% full-time | ~48 hours |
| **QA Engineer** | 20% full-time | ~24 hours |
| **Product Manager** | 10% full-time | ~12 hours |
| **Total** | | ~179 hours |

**Estimated Phase 0 Development Cost:** $11,000 - $22,000 (depending on rates)

---

**Phase 1 (Core AI PM - 13 weeks)**

| Role | Time Allocation | Estimated Hours |
|------|----------------|----------------|
| **Backend Developer (Laravel)** | 60% full-time | ~310 hours |
| **Frontend Developer (Next.js)** | 50% full-time | ~260 hours |
| **UX/UI Designer** | 25% full-time | ~130 hours |
| **QA Engineer** | 30% full-time | ~156 hours |
| **Product Manager** | 20% full-time | ~104 hours |
| **Total** | | ~960 hours |

**Estimated Phase 1 Development Cost:** $60,000 - $120,000 (depending on rates)

---

**Phase 2 (AI Follow-Up Email Generator - 4 weeks)**

| Role | Time Allocation | Estimated Hours |
|------|----------------|----------------|
| **Backend Developer (Laravel)** | 70% full-time | ~110 hours |
| **Frontend Developer (Next.js)** | 60% full-time | ~95 hours |
| **UX/UI Designer** | 40% full-time | ~65 hours |
| **QA Engineer** | 30% full-time | ~48 hours |
| **Product Manager** | 15% full-time | ~24 hours |
| **Total** | | ~342 hours |

**Estimated Phase 2 Development Cost:** $21,000 - $43,000 (depending on rates)

---

**Total Development Cost (All Phases):** $92,000 - $185,000  
*(Phase 0: $11K-$22K + Phase 1: $60K-$120K + Phase 2: $21K-$43K)*

#### 19.2 Operational Costs (Monthly)

| Item | Estimated Cost |
|------|---------------|
| **OpenAI API Usage** | $200 - $500 |
| **Email Sending** | $20 - $50 |
| **Additional Server Resources** | $50 - $100 |
| **Monitoring & Logging** | $20 - $50 |
| **Total Monthly** | $290 - $700 |

**Estimated Annual Operational Cost:** $3,500 - $8,400

**Cost Assumptions:**
- 100 active projects
- Weekly AI analysis (4x per month)
- 10 admin users receiving emails
- Average 1000 tokens per AI analysis
- GPT-4-Turbo pricing: ~$0.01 per 1K input tokens, ~$0.03 per 1K output tokens

#### 19.3 ROI Calculation

**Estimated Time Savings per Admin:**
- Manual project monitoring: 10 hours/week
- With AI PM: 3 hours/week
- **Savings:** 7 hours/week per admin = ~30 hours/month per admin

**Value Calculation:**
- Admin hourly rate: $50 - $100/hour
- Monthly value per admin: $1,500 - $3,000
- For 10 admins: **$15,000 - $30,000/month**

**Break-even:** Month 1 (operational costs covered by time savings)

---

### 20. Launch Plan

#### 20.1 Pre-Launch (Weeks 11-12)

**Beta Testing:**
- Invite 3-5 friendly admin users
- Provide beta access with feedback form
- Weekly check-ins for feedback
- Iterate based on input

**Documentation:**
- Finalize user guides
- Record video tutorials
- Create FAQ document
- Prepare launch announcement

**Marketing Materials:**
- Feature spotlight page
- Email announcement draft
- In-app banner design
- Social media posts (if applicable)

#### 20.2 Launch Day (Week 13, Day 1)

**Technical:**
- Deploy to production during low-traffic window
- Monitor error logs and performance
- Ensure scheduled jobs are configured
- Verify email deliverability

**Communications:**
- Send announcement email to all admins
- Display in-app banner/modal introducing feature
- Post to company blog/website
- Update changelog

**Support:**
- Have engineering team on standby
- Prepare support team with FAQ
- Monitor user feedback channels

#### 20.3 Post-Launch (Weeks 14-16)

**Week 1:**
- Monitor adoption metrics daily
- Collect initial user feedback
- Address critical bugs/issues
- Send follow-up email with tips

**Week 2:**
- Analyze first weekly reports sent
- Review email open rates
- Conduct user interviews (5-10 admins)
- Iterate on AI prompts based on feedback

**Week 3-4:**
- Publish first AI PM performance report internally
- Identify improvement opportunities
- Plan quick wins for next sprint
- Survey user satisfaction

---

### 21. Success Stories & Use Cases

#### Use Case 1: Preventing Project Failure

**Scenario:**  
A video production project has 3 tasks overdue and the deadline is in 2 days. The assigned tasker hasn't logged in for 5 days.

**AI PM Action:**
- Detects issue during scheduled analysis
- Flags as **Critical** severity
- Sends immediate alert email to admin
- Recommends: "Reassign tasks to team member with capacity, extend deadline, or contact tasker urgently"

**Outcome:**  
Admin sees alert, immediately contacts tasker (who had forgotten), reassigns one task, and extends deadline by 2 days. Project completes successfully with only minor delay.

**Value:** Prevented complete project failure and client dissatisfaction.

---

#### Use Case 2: Resource Reallocation

**Scenario:**  
Weekly report shows that 3 users have 8+ active tasks each while 2 other users have only 1-2 tasks.

**AI PM Action:**
- Identifies resource imbalance in weekly analysis
- Flags as **Medium** severity
- Recommendation: "Consider redistributing tasks from overloaded users to underutilized team members"

**Outcome:**  
Admin reviews workload distribution, reassigns 5 tasks to underutilized users, improving overall team velocity by 20%.

**Value:** Optimized team capacity and reduced burnout risk.

---

#### Use Case 3: Pattern Recognition

**Scenario:**  
AI detects that design tasks consistently take 30% longer than estimated across multiple projects.

**AI PM Action:**
- Identifies trend over 4-week period
- Flags as **Low** severity (informational)
- Recommendation: "Adjust time estimates for design tasks to improve planning accuracy"

**Outcome:**  
Admin updates task templates to reflect realistic design timelines, improving future project forecasting.

**Value:** Better planning and more accurate client commitments.

---

### 22. Glossary

**AI Project Manager (AI PM):** The intelligent oversight feature that monitors projects and provides insights.

**Health Score:** Numerical rating (0-10) indicating overall project health based on multiple metrics.

**Analysis Depth:** Configuration option controlling how thorough AI analysis is (Quick, Standard, Deep).

**Issue Severity:** Classification of detected problems (Low, Medium, High, Critical).

**Executive Summary:** High-level overview of project health and key insights generated by AI.

**On-Demand Analysis:** AI analysis triggered manually by admin rather than on schedule.

**Stale Project:** Project with no task activity for extended period (default 7 days).

**Checklist Completion Rate:** Percentage of checklist items marked complete across all tasks in a project.

**Task Velocity:** Rate at which tasks are completed over time (tasks per week).

**Threshold:** Configurable value that triggers issue detection when exceeded.

**Prompt Engineering:** Process of crafting effective prompts for AI to generate quality insights.

**Token:** Unit of text processed by OpenAI API (roughly 0.75 words per token).

---

### 23. Appendices

#### Appendix A: Sample AI Prompts

**Weekly Report Prompt Template:**
```
You are an experienced project manager analyzing a portfolio of projects. Based on the data provided, generate a concise weekly executive report.

DATA SUMMARY:
- Total Projects: {count}
- Projects On Track: {on_track}
- Projects At Risk: {at_risk}
- Projects Behind: {behind}
- Total Tasks: {total_tasks}
- Tasks Completed This Week: {completed_tasks}
- Overdue Tasks: {overdue_tasks}
- Average Checklist Completion: {checklist_avg}%

CRITICAL ISSUES:
{critical_issues_list}

INSTRUCTIONS:
1. Provide an overall health assessment (1-10 scale)
2. Summarize the week in 2-3 sentences
3. Highlight the top 3 most critical issues requiring immediate attention
4. Identify any positive trends or achievements
5. Give 3 prioritized recommendations for the admin
6. Keep tone professional but approachable

FORMAT:
- Use bullet points for clarity
- Be specific and actionable
- Focus on what the admin can control
```

**Individual Project Analysis Prompt:**
```
Analyze the following project and provide actionable insights:

PROJECT: {project_name}
CLIENT: {client_name}
TIMELINE: {start_date} to {end_date} ({days_remaining} days remaining)

METRICS:
- Tasks: {completed_tasks}/{total_tasks} complete
- Checklists: {checklist_complete}% complete
- Overdue: {overdue_count} tasks
- Status: {status_distribution}
- Team: {team_member_count} members, last activity {last_activity}

SPECIFIC ISSUES:
{detected_issues}

Provide:
1. Health Score (1-10) with brief justification
2. Current project status (On Track/At Risk/Behind/Critical)
3. Top 3 specific concerns for this project
4. Root cause analysis if project is behind
5. 2-3 concrete recommendations
6. Estimated likelihood of on-time completion

Be specific and reference actual data points.
```

#### Appendix B: Sample Email Template (HTML Structure)

```html
<!DOCTYPE html>
<html>
<head>
  <title>AI Project Manager Weekly Report</title>
  <style>
    /* Responsive email CSS */
    body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
    .container { max-width: 600px; margin: 0 auto; background: white; }
    .header { background: #4F46E5; color: white; padding: 20px; }
    .health-score { font-size: 48px; font-weight: bold; }
    .critical { color: #EF4444; }
    .medium { color: #F59E0B; }
    .low { color: #10B981; }
    .issue-card { border-left: 4px solid; padding: 15px; margin: 10px 0; }
    .button { background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>📊 Weekly AI Project Manager Report</h1>
      <p>Week of {{date_range}}</p>
    </div>
    
    <div class="summary">
      <div class="health-score">{{health_score}}/10</div>
      <p>{{ai_summary}}</p>
      
      <div class="stats">
        <div class="stat">🔴 {{critical_count}} Critical</div>
        <div class="stat">🟡 {{medium_count}} Medium</div>
        <div class="stat">🟢 {{on_track_count}} On Track</div>
      </div>
    </div>
    
    <div class="critical-issues">
      <h2>⚠️ Needs Immediate Attention</h2>
      {{#each critical_issues}}
      <div class="issue-card critical">
        <h3>{{project_name}}</h3>
        <p>{{description}}</p>
        <p><strong>Recommendation:</strong> {{recommendation}}</p>
        <a href="{{link}}" class="button">View Project →</a>
      </div>
      {{/each}}
    </div>
    
    <div class="insights">
      <h2>💡 Key Insights</h2>
      {{{ai_insights}}}
    </div>
    
    <div class="recommendations">
      <h2>✅ Recommended Actions</h2>
      <ol>
        {{#each recommendations}}
        <li>{{this}}</li>
        {{/each}}
      </ol>
    </div>
    
    <div class="footer">
      <a href="{{dashboard_link}}" class="button">View Full Dashboard</a>
      <p><a href="{{preferences_link}}">Email Preferences</a></p>
    </div>
  </div>
</body>
</html>
```

#### Appendix C: Database Indexes for Performance

```sql
-- High-priority indexes for AI PM queries

-- Fast lookup of recent analyses
CREATE INDEX idx_ai_analyses_recent 
ON ai_project_analyses(created_at DESC, analysis_type);

-- Efficient project health queries
CREATE INDEX idx_ai_analyses_health 
ON ai_project_analyses(overall_status, health_score);

-- Quick unresolved issue queries
CREATE INDEX idx_ai_issues_unresolved 
ON ai_project_issues(resolved_at, severity, project_id)
WHERE resolved_at IS NULL;

-- Fast project + date range queries
CREATE INDEX idx_projects_active 
ON projects(start_date, end_date, status_id);

-- Task status aggregation
CREATE INDEX idx_tasks_project_status 
ON tasks(project_id, status_id, end_date);

-- Checklist completion queries
CREATE INDEX idx_checklist_answers_task 
ON checklist_answers(task_id, completed, created_at);

-- Email log queries
CREATE INDEX idx_email_logs_recipient_type 
ON ai_pm_email_logs(recipient_user_id, email_type, sent_at);
```

---

## Document Change Log

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 2025-09-30 | Initial PRD creation | Benjamin Simkin |
| 1.1 | 2025-09-30 | Added enhanced monitoring features: task rejection tracking, task engagement/view tracking, platform usage patterns, historical duration comparisons, positive/negative signals, velocity decline detection, task abandonment detection, no progression tracking | Benjamin Simkin |
| 1.2 | 2025-09-30 | Added Phase 2: AI-Powered Follow-Up Email Generator with complete specification including checkbox selection interface, recipient management, AI generation logic, customization options, tracking capabilities, database schema, API endpoints, UI mockups, and 4-week implementation plan | Benjamin Simkin |
| 1.3 | 2025-09-30 | **CRITICAL UPDATE:** Identified that VendorConnect does NOT currently collect most required data. Added Phase 0 (3 weeks) for data collection infrastructure: task_views, task_rejections, user_sessions, project_metrics_baseline tables. Added database schemas, implementation plan, and cost estimates ($11K-$22K). Updated total project timeline to 20 weeks and cost to $92K-$185K. | Benjamin Simkin |
| 1.4 | 2025-09-30 | **FEATURE ENHANCEMENTS:** Added Smart Compliance Checker (6 QA checks including blocker keyword detection via NLP, attachment validation, subtask dependencies), Per-Person Accountability View with heat map visualization and individual performance metrics, Automated Nudges to team members (5 nudge types with configurable settings and opt-out), Daily quick pulse option for high-priority projects. Added 5 new issue types: blocker keywords detected, missing attachments, parent task closed prematurely, unassigned critical task, compliance check failed. Total issue types now 24. | Benjamin Simkin |

---

## Approvals

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Product Owner | __________ | __________ | _____ |
| Engineering Lead | __________ | __________ | _____ |
| Design Lead | __________ | __________ | _____ |
| Stakeholder | __________ | __________ | _____ |

---

**END OF DOCUMENT**
