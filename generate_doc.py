from docx import Document
from docx.shared import Pt
from docx.enum.text import WD_ALIGN_PARAGRAPH

doc = Document()

# Remove default styles and set simple font
style = doc.styles['Normal']
style.font.name = 'Calibri'
style.font.size = Pt(11)

def heading(text, level=1):
    p = doc.add_heading(text, level=level)
    p.runs[0].font.color.rgb = None  # no color
    return p

def body(text):
    doc.add_paragraph(text)

def bullet(text):
    doc.add_paragraph(text, style='List Bullet')

def numbered(text):
    doc.add_paragraph(text, style='List Number')

def add_table(headers, rows):
    table = doc.add_table(rows=1 + len(rows), cols=len(headers))
    table.style = 'Table Grid'
    hdr = table.rows[0].cells
    for i, h in enumerate(headers):
        hdr[i].text = h
        hdr[i].paragraphs[0].runs[0].bold = True
    for row_data in rows:
        row = table.add_row().cells
        for i, val in enumerate(row_data):
            row[i].text = val
    doc.add_paragraph()

# ── TITLE ──
doc.add_heading('Barina Land Project', 0)
body('Development Workflow Model')
doc.add_paragraph()

# ── 1 ──
heading('1. The Big Picture', 1)
body('Building this system involves four layers of work that move in order:')
body('Concept  →  Design  →  Build  →  Deploy')
body('The Concept phase is complete. Each of the four delivery phases (Foundation, Community, Institutions, Commerce) goes through Design, Build, and Deploy before the next phase begins. This ensures the system is usable at every stage.')
doc.add_paragraph()

# ── 2 ──
heading('2. How Each Phase Moves', 1)
body('Every phase follows this sequence:')
steps = [
    'Phase Kickoff',
    'Design Sprint — wireframes, user flows, and screens produced',
    'Review and Sign-Off — client confirms before anything is built',
    'Database First — schema written and tested',
    'Backend and API — logic and data operations built',
    'Frontend — screens connected to live data',
    'Testing — functional and user testing',
    'Deploy to Staging — real environment, not localhost',
    'Client Sign-Off — client tests and approves',
    'Deploy to Production — live to the world',
    'Monitor and Patch — bugs fixed, feedback collected',
    'Next Phase Kickoff',
]
for s in steps:
    numbered(s)
doc.add_paragraph()

# ── 3 ──
heading('3. Work Breakdown by Role', 1)
add_table(
    ['Stage', 'Client', 'Project Lead', 'Developer', 'Designer'],
    [
        ['Phase kickoff', 'Confirm scope and priorities', 'Break into tasks', 'Review tech requirements', 'Begin wireframes'],
        ['Design sprint', 'Review and approve screens', 'Coordinate feedback', 'Flag technical constraints', 'Deliver final designs'],
        ['Database', 'Review data structure', 'Approve schema', 'Write and test schema', '—'],
        ['Backend', 'Review API endpoints list', 'QA logic', 'Build APIs', '—'],
        ['Frontend', 'Preview builds', 'Coordinate reviews', 'Build UI', 'Support with assets'],
        ['Testing', 'Participate in UAT', 'Manage bug log', 'Fix issues', 'Fix UI inconsistencies'],
        ['Staging deploy', 'Test as a real user', 'Sign off checklist', 'Deploy and monitor', '—'],
        ['Production deploy', 'Final approval', 'Coordinate launch', 'Deploy', '—'],
        ['Monitor and patch', 'Report issues', 'Triage and prioritize', 'Fix and release', '—'],
    ]
)

# ── 4 ──
heading('4. Task Organization', 1)

heading('Task Types', 2)
items = [
    'Feature — new functionality being built for the first time',
    'Fix — something broken that needs correcting',
    'Improvement — existing feature made better',
    'Content — text, images, or data being added',
    'Config — settings, environment, or infrastructure',
]
for i in items:
    bullet(i)

heading('Task Status Flow', 2)
body('Backlog  →  To Do  →  In Progress  →  In Review  →  Done')
statuses = [
    'Backlog — known but not yet scheduled',
    'To Do — scheduled for the current sprint, not yet started',
    'In Progress — actively being worked on',
    'In Review — built, waiting for review or testing',
    'Done — approved and merged',
]
for s in statuses:
    bullet(s)

heading('Priority Levels', 2)
priorities = [
    'P1 Critical — blocks everything else, fixed immediately',
    'P2 High — needed before the phase goes live',
    'P3 Medium — important but not blocking',
    'P4 Low — nice to have, scheduled when there is room',
]
for p in priorities:
    bullet(p)
doc.add_paragraph()

# ── 5 ──
heading('5. Sprint Structure', 1)
body('Work runs in two-week sprints. Each sprint has a clear goal tied to the current phase.')
sprint = [
    'Day 1 — Sprint planning: tasks assigned, goals set',
    'Days 2 to 9 — Active building',
    'Day 10 — Internal review and testing',
    'Day 11 — Fixes from review',
    'Day 12 — Staging deploy and client testing',
    'Day 13 — Client feedback addressed',
    'Day 14 — Sprint close, retrospective, next sprint planned',
]
for s in sprint:
    bullet(s)
body('Each sprint produces something real — a working screen, a working feature, or a live API. No sprint ends with nothing to show.')
doc.add_paragraph()

# ── 6 ──
heading('6. Decision-Making Flow', 1)
body('Not every decision needs client input. The following rules apply:')
heading('Client is always consulted on:', 2)
client_decisions = [
    'Scope changes — something added to or removed from a phase',
    'Design direction — layouts, flows, content',
    'Priority shifts — swapping what goes into a sprint',
    'Anything that affects members or the public',
]
for c in client_decisions:
    bullet(c)
heading('Team decides independently on:', 2)
team_decisions = [
    'Which library or tool to use for a technical problem',
    'Code architecture and folder structure',
    'Database optimizations',
    'Bug fixes that do not change visible behaviour',
]
for t in team_decisions:
    bullet(t)
doc.add_paragraph()

# ── 7 ──
heading('7. Communication Rhythm', 1)
add_table(
    ['Touchpoint', 'Frequency', 'Format', 'Who'],
    [
        ['Progress update', 'Every 3 days', 'Short written summary', 'Project Lead to Client'],
        ['Sprint review', 'Every 2 weeks', 'Live walkthrough of what was built', 'Full team and Client'],
        ['Design review', 'Per design sprint', 'Share screens, client comments', 'Designer and Client'],
        ['Bug report', 'As needed', 'Short note with screenshot', 'Developer to Project Lead, Client if P1 or P2'],
        ['Phase sign-off', 'End of each phase', 'Full walkthrough before going live', 'Client'],
    ]
)

# ── 8 ──
heading('8. Environments', 1)
add_table(
    ['Environment', 'Purpose', 'Who Accesses It'],
    [
        ['Local', 'Developer machine, daily building and testing', 'Developers only'],
        ['Staging', 'Shared test environment, mirrors production', 'Team and Client for reviews'],
        ['Production', 'Live system with real members and real data', 'Everyone'],
    ]
)
body('Nothing goes to production without staging sign-off from the client. No exceptions.')
doc.add_paragraph()

# ── 9 ──
heading('9. Version Control Flow', 1)
body('How code is managed so nothing breaks and nothing gets lost.')
branches = [
    'main branch — production, always stable, protected',
    'staging branch — what the client tests, merged into main after sign-off',
    'feature branches — one branch per feature, merged into staging when ready',
]
for b in branches:
    bullet(b)
heading('Rules:', 2)
rules = [
    'No one pushes directly to main or staging',
    'Every feature gets its own branch',
    'Code is reviewed before merging',
    'Every merge to main is tagged with a version number',
]
for r in rules:
    bullet(r)
doc.add_paragraph()

# ── 10 ──
heading('10. Phase-by-Phase Sprint Schedule', 1)

heading('Phase 1 — Foundation', 2)
p1 = [
    'Sprint 1: Database schema, authentication, member registration',
    'Sprint 2: Plot map and admin panel (land and finance roles)',
    'Sprint 3: M-Pesa payments and receipt generation',
    'Sprint 4: Public website and progress tracker',
    'Staging review → Client sign-off → Launch Phase 1',
]
for s in p1:
    bullet(s)

heading('Phase 2 — Community', 2)
p2 = [
    'Sprint 5: Community hub — events, projects, forums',
    'Sprint 6: Governance and voting, member referrals',
    'Sprint 7: Flutterwave, Stripe, and diaspora payment flows',
    'Sprint 8: Infrastructure module and contractor directory',
    'Staging review → Client sign-off → Launch Phase 2',
]
for s in p2:
    bullet(s)

heading('Phase 3 — Institutions', 2)
p3 = [
    'Sprint 9: School — enrollment, fees, notice board',
    'Sprint 10: Clinic — appointments, staff roster',
    'Sprint 11: Farm produce and pre-orders, activity bookings',
    'Sprint 12: Utilities billing and full reporting dashboard',
    'Staging review → Client sign-off → Launch Phase 3',
]
for s in p3:
    bullet(s)

heading('Phase 4 — Commerce', 2)
p4 = [
    'Sprint 13: Supermarket — catalog, orders, delivery',
    'Sprint 14: Restaurants and shops',
    'Sprint 15: Commercial space rentals and community marketplace',
    'Sprint 16: Full analytics and investor reporting suite',
    'Staging review → Client sign-off → Launch Phase 4',
]
for s in p4:
    bullet(s)
doc.add_paragraph()

# ── 11 ──
heading('11. Quality Checkpoints', 1)
body('Before anything moves to the next stage it must pass:')
add_table(
    ['Checkpoint', 'What Is Checked'],
    [
        ['Code review', 'Another developer reads the code before it merges'],
        ['Functional test', 'Feature works as described in the task'],
        ['Edge case test', 'Handles bad input, empty data, and slow connections'],
        ['Mobile test', 'Works correctly on a phone — critical for the Kenya market'],
        ['Payment test', 'M-Pesa and gateway flows tested with real sandbox transactions'],
        ['Security check', 'No exposed data, no unauthorised access possible'],
        ['Client UAT', 'Client uses the system as a real member would'],
    ]
)

# ── 12 ──
heading('12. What the Client Owns', 1)
body('At every phase, these decisions belong to the client:')
owns = [
    'What goes into each phase — priorities can be adjusted at any time',
    'Design approval — nothing goes live that the client has not seen',
    'Content — text, images, property data, community information',
    'Member communications — what gets sent to members and when',
    'Go or No-Go on every production launch',
    'Admin user creation — who gets which admin role',
]
for o in owns:
    numbered(o)
doc.add_paragraph()

# ── 13 ──
heading('13. Summary', 1)
body('Organize → Design → Build → Test → Ship')
body('This cycle repeats for all four phases, tightening each time as the team becomes more familiar with the codebase and the client becomes more confident reviewing builds.')
body('Phase 1 is next. It begins with Sprint 1: database schema, authentication, and member registration.')

doc.save('/home/user/bildfie/Barina_Land_Project_Workflow.docx')
print("Done")
