from docx import Document
from docx.shared import Pt, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

doc = Document()

# --- Styles ---
style = doc.styles['Normal']
style.font.name = 'Calibri'
style.font.size = Pt(11)

def heading1(text):
    p = doc.add_heading(text, level=1)
    p.runs[0].font.color.rgb = RGBColor(0x1A, 0x1A, 0x2E)
    return p

def heading2(text):
    p = doc.add_heading(text, level=2)
    p.runs[0].font.color.rgb = RGBColor(0x16, 0x53, 0x28)
    return p

def heading3(text):
    p = doc.add_heading(text, level=3)
    p.runs[0].font.color.rgb = RGBColor(0x2C, 0x2C, 0x2C)
    return p

def para(text, bold=False, italic=False):
    p = doc.add_paragraph()
    run = p.add_run(text)
    run.bold = bold
    run.italic = italic
    return p

def bullet(text, level=0):
    p = doc.add_paragraph(text, style='List Bullet')
    p.paragraph_format.left_indent = Inches(0.25 * (level + 1))
    return p

def add_table(headers, rows):
    table = doc.add_table(rows=1 + len(rows), cols=len(headers))
    table.style = 'Table Grid'
    # Header row
    hdr = table.rows[0].cells
    for i, h in enumerate(headers):
        hdr[i].text = h
        for run in hdr[i].paragraphs[0].runs:
            run.bold = True
        # shade header
        tc = hdr[i]._tc
        tcPr = tc.get_or_add_tcPr()
        shd = OxmlElement('w:shd')
        shd.set(qn('w:val'), 'clear')
        shd.set(qn('w:color'), 'auto')
        shd.set(qn('w:fill'), '165328')
        tcPr.append(shd)
        for run in hdr[i].paragraphs[0].runs:
            run.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
    # Data rows
    for r_idx, row_data in enumerate(rows):
        row = table.rows[r_idx + 1].cells
        for i, cell_text in enumerate(row_data):
            row[i].text = cell_text
    doc.add_paragraph()

def code_block(text):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Inches(0.4)
    run = p.add_run(text)
    run.font.name = 'Courier New'
    run.font.size = Pt(9)
    run.font.color.rgb = RGBColor(0x2C, 0x2C, 0x2C)

def divider():
    doc.add_paragraph('─' * 60)

# ============================================================
# COVER
# ============================================================
doc.add_paragraph()
t = doc.add_paragraph()
t.alignment = WD_ALIGN_PARAGRAPH.CENTER
run = t.add_run('CECOME')
run.bold = True
run.font.size = Pt(28)
run.font.color.rgb = RGBColor(0x16, 0x53, 0x28)

t2 = doc.add_paragraph()
t2.alignment = WD_ALIGN_PARAGRAPH.CENTER
run2 = t2.add_run('Centre for Community Mobilization and Empowerment')
run2.font.size = Pt(14)
run2.italic = True

t3 = doc.add_paragraph()
t3.alignment = WD_ALIGN_PARAGRAPH.CENTER
run3 = t3.add_run('Website Concept & Operations Model')
run3.bold = True
run3.font.size = Pt(16)

t4 = doc.add_paragraph()
t4.alignment = WD_ALIGN_PARAGRAPH.CENTER
run4 = t4.add_run('Kisii, Kenya  ·  2025')
run4.font.size = Pt(11)
run4.font.color.rgb = RGBColor(0x88, 0x88, 0x88)

doc.add_page_break()

# ============================================================
# PART A — WEBSITE CONCEPT
# ============================================================
heading1('PART A — WEBSITE CONCEPT')
divider()

# STEP 1
heading2('STEP 1 — Who CECOME Is')

add_table(
    ['Field', 'Details'],
    [
        ['Full name', 'Centre for Community Mobilization and Empowerment'],
        ['Type', 'Women-led, women-managed NGO'],
        ['Registered', 'Kenya, 2012'],
        ['Location', 'Credit Bank Building, Hospital Road, Kisii County'],
        ['Tagline', '"A haven for women and girls to be empowered"'],
    ]
)

heading3('The Problem CECOME Exists to Solve')
para('Kisii County faces entrenched cultural and social challenges that put girls and women at serious risk:')
bullet('FGM remains practiced — largely as a private family affair')
bullet('SGBV is widespread and underreported')
bullet("Kisii County's only GBV rescue centre exists but remains non-functional — no basic equipment")
bullet('No county-level gender policy exists to guide intervention or accountability')
bullet('Girls face early and forced marriages, school dropout, and loss of agency')
bullet('Women lack economic power, making them more vulnerable to abuse')
para('CECOME exists to change this — through community action, not outside impositions.', italic=True)

heading3('What CECOME Does — Programs')

para('1. SGBV Prevention', bold=True)
para('Community sensitization and empowerment on the effects of SGBV and harmful cultural practices. Engages community volunteers, teachers, religious leaders, local administration, and media partners as change agents.')

para('2. Alternative Rites of Passage (ARP)', bold=True)
para('Girls receive culturally meaningful teachings and life skills as a safe, dignified alternative to FGM. Run as school holiday mentorship camps. 100 girls go through ARP annually. Proven to keep 98% of girls free from FGM and 99% free from child/early/forced marriage.')

para('3. Young Women\'s Leadership', bold=True)
para('Trains young women as Trainers of Trainers (TOTs) to lead community dialogues. 280 young women TOTs trained to date. Graduates go on to take up civic and political roles — e.g. Neema Nyaundi, a CECOME alumna, now serves as Assistant Coordinator for Kisii County in a national political party.')

para('4. Child Protection', bold=True)
para('Information sharing sessions for boys and girls. 500 boys and girls positively impacted. Works with schools and community groups.')

para('5. Civic Education & Advocacy', bold=True)
para('Campaigns for adoption of a county-level Gender Policy for Kisii. Advocates for operationalization of the Kisii GBV rescue centre. Young women from CECOME have written opinion pieces in national newspapers. Holds radio and TV talk shows, Facebook live sessions on national awareness days.')

para('6. Economic Empowerment', bold=True)
para("Encourages women to form village savings and lending groups. Women use savings to start small businesses. Works with ISF local livelihood partners and the Muungano Gender Forum.")

para('7. Consultancy Services', bold=True)
para('Offers professional consultancy to county government and community forums on:')
bullet('Health')
bullet('Water and sanitation')
bullet('Child protection')
bullet('Gender and development')
bullet('Monitoring and evaluation')
bullet('Civic education and participatory training methodologies')
para('Has consulted with KICCOF and the county government\'s public participation department on devolution and public participation.')

heading3('Impact Numbers')
add_table(
    ['Metric', 'Figure'],
    [
        ['Young women TOTs trained', '280'],
        ['Community champions trained', '20'],
        ['Girls through ARP annually', '100'],
        ['Boys & girls reached (info sessions)', '500+'],
        ['Girls free of FGM after ARP', '98%'],
        ['Girls free of early marriage after ARP', '99%'],
        ['Years of operation', '13 (since 2012)'],
    ]
)

heading3('Partners & Collaborators')
bullet('YW4A Programme (Young Women for Awareness, Agency, Advocacy & Accountability)')
bullet('Equality Now — strengthened legal and media advocacy capacity')
bullet('ISF (Inspiration for Sustainability Foundation) — livelihoods and ARP curriculum')
bullet('Solidaarisuus (Finnish development NGO)')
bullet('Muungano Gender Forum — coordination on gender issues')
bullet('KICCOF (Kisii County Community Forum) — civic education')
bullet('Kisii County Government — public participation and gender sector working group')
bullet('Local radio and TV stations — media outreach')
bullet('GBV Prevention Network Africa — member organization')

heading3('What Makes CECOME Distinct')
bullet('It is not an outside organization — it is from the community, for the community')
bullet('It works with cultural structures rather than against them (ARP vs. forced abandonment of FGM)')
bullet('It produces local leaders — alumni take up civic, political, and advocacy roles')
bullet('It combines direct service (ARP camps, helplines) with systemic change (gender policy advocacy)')
bullet('It offers consultancy — a revenue stream that also builds government and community capacity')
bullet('It operates across multiple sectors: protection, education, health, livelihoods, governance')

# STEP 2
heading2('STEP 2 — Define Who the Website Serves')
para('Five distinct audiences will visit. Every decision about what goes on the website answers: which of these five does this serve?')
add_table(
    ['Audience', 'Primary Need', 'Key Action'],
    [
        ['Donor', 'Trust + emotional connection', 'Donate'],
        ['Funder / Grant-maker', 'Credibility + data', 'Download report / send inquiry'],
        ['Volunteer', 'Clarity on roles + process', 'Apply'],
        ['Beneficiary', 'Immediate help + safety', 'Call helpline / find referral'],
        ['Media / Press', 'Facts + visuals + contact', 'Download media kit / contact spokesperson'],
    ]
)

# STEP 3
heading2('STEP 3 — What Each Audience Needs')
add_table(
    ['Audience', 'Core Need', 'Key Action'],
    [
        ['Donor', 'Trust + emotional connection', 'Donate'],
        ['Funder', 'Credibility + data', 'Download report / send inquiry'],
        ['Volunteer', 'Clarity on roles + process', 'Apply'],
        ['Beneficiary', 'Immediate help + safety', 'Call helpline / find referral'],
        ['Media', 'Facts + visuals + contact', 'Download media kit / contact spokesperson'],
    ]
)

# STEP 4
heading2('STEP 4 — What the Website Must Contain')
add_table(
    ['#', 'Section', 'Purpose'],
    [
        ['1', 'Home', 'Entry point — directs each person to their path'],
        ['2', 'About', 'Who CECOME is, team, history, partners, governance'],
        ['3', 'What We Do', 'Each of the 7 programs, one page each'],
        ['4', 'Our Impact', 'Stories, data, reports, gallery'],
        ['5', 'Get Help', 'Helpline, safe spaces, referrals — for beneficiaries'],
        ['6', 'Get Involved', 'Donate, volunteer, partner/fund us'],
        ['7', 'Media Room', 'Press releases, photos, spokesperson contact'],
        ['8', 'News & Events', 'What is happening now'],
        ['9', 'Contact', 'For everyone else'],
    ]
)

# STEP 5
heading2('STEP 5 — How Each Audience Moves Through the Site')

heading3('Donor')
code_block(
    "Arrives\n"
    "  → reads mission\n"
    "  → reads a girl's story\n"
    "  → checks partners and registration\n"
    "  → donates\n"
    "  → receives confirmation\n"
    "  → stays connected via newsletter"
)

heading3('Funder')
code_block(
    "Arrives\n"
    "  → checks About\n"
    "  → verifies registration, team, partners\n"
    "  → downloads annual report\n"
    "  → reads specific program (e.g. ARP)\n"
    "  → fills partnership inquiry form\n"
    "  → CECOME responds within 48 hrs"
)

heading3('Volunteer')
code_block(
    "Arrives\n"
    "  → Get Involved → Volunteer\n"
    "  → reads roles and time commitment\n"
    "  → fills application\n"
    "  → receives onboarding email"
)

heading3('Beneficiary')
code_block(
    "Arrives in distress\n"
    "  → Get Help page\n"
    "  → sees helpline number immediately — no scrolling\n"
    "  → finds nearest safe space or referral contact\n"
    "     (hospital · police · legal aid)"
)

heading3('Press')
code_block(
    "Arrives\n"
    "  → Media Room\n"
    "  → downloads fact sheet and photos\n"
    "  → contacts spokesperson\n"
    "  → quotes CECOME → free public awareness"
)

# STEP 6
heading2('STEP 6 — What Keeps the Website Alive')
add_table(
    ['Frequency', 'Content Action'],
    [
        ['Monthly', 'One story from the field — real person, real change'],
        ['Quarterly', 'Program update or brief report'],
        ['Per event', 'Before and after every ARP camp, training, or campaign'],
        ['Annually', 'Full annual report upload'],
    ]
)
para('This content feeds the website, the newsletter, and social media — all from the same source.', italic=True)

# STEP 7
heading2('STEP 7 — What Builds Trust')
add_table(
    ['Trust Signal', 'Where It Goes'],
    [
        ['NGO registration number', 'Footer + About page'],
        ['Names and photos of leadership team', 'About → Our Team'],
        ['List of current and past partners / funders', 'About → Partners'],
        ['Annual reports (downloadable)', 'Our Impact → Reports'],
        ['Financial summary or audit reference', 'Financials & Governance page'],
        ['Real stories with real names (where safe)', 'Our Impact → Stories'],
        ['Media coverage links', 'Media Room + News'],
    ]
)

# STEP 8
heading2('STEP 8 — The One Job of Each Page')
add_table(
    ['Page', 'One Job'],
    [
        ['Home', 'Make every visitor feel seen and direct them forward'],
        ['About', 'Build trust'],
        ['What We Do', 'Explain the work clearly'],
        ['Our Impact', 'Prove the work is real'],
        ['Get Help', 'Connect someone in need to immediate support'],
        ['Get Involved', 'Convert interest into action'],
        ['Media Room', 'Make press coverage easy'],
        ['News & Events', 'Show the org is active'],
        ['Contact', 'Remove every barrier to reaching CECOME'],
    ]
)

doc.add_page_break()

# ============================================================
# PART B — OPERATIONS MODEL
# ============================================================
heading1('PART B — OPERATIONS MODEL')
divider()
para('The operations model answers one question: once the website is live, how does it run?')
para('It covers four things:')
bullet('Who runs what')
bullet('How content moves from field to website')
bullet('How people (donors, volunteers, beneficiaries) are managed')
bullet('How the website is kept honest and accountable')

# STEP 1
heading2('STEP 1 — The Team Behind the Website')
para('The website does not run itself. These are the minimum roles needed:')
add_table(
    ['Role', 'Responsibility', 'Notes'],
    [
        ['Website Manager', 'Updates pages, publishes content, monitors traffic', 'Comms / admin staff'],
        ['Content Producer', 'Writes stories, takes photos, documents field activities', 'Program officers'],
        ['Donor Coordinator', 'Manages donation records, sends receipts, follows up', 'Finance / admin'],
        ['Volunteer Coordinator', 'Receives applications, onboards, communicates', 'Programs staff'],
        ['Technical Contact', 'Fixes bugs, renews hosting, manages backups', 'External or IT volunteer'],
    ]
)
para('For a small NGO like CECOME, one person can hold two roles — but the responsibilities must be clearly assigned, not assumed.', italic=True)

# STEP 2
heading2('STEP 2 — Content Flow (Field → Website)')
code_block(
    "ACTIVITY HAPPENS IN THE FIELD\n"
    "(ARP camp · GBV sensitization · radio show · training)\n"
    "            ↓\n"
    "Program officer documents it\n"
    "(photos · key numbers · one person's story · date)\n"
    "            ↓\n"
    "Content Producer drafts a post or story\n"
    "(500 words max · 1–2 photos · one clear message)\n"
    "            ↓\n"
    "Website Manager reviews and publishes\n"
    "            ↓\n"
    "  ├── Posted on website (News / Stories)\n"
    "  ├── Shared on Facebook / X / Instagram\n"
    "  └── Included in next monthly newsletter"
)

para('Minimum content output:')
bullet('1 story or news post per month')
bullet('1 program update per quarter')
bullet('1 event post per activity (before + after)')
bullet('1 annual report per year')

# STEP 3
heading2('STEP 3 — Donor Management Flow')
code_block(
    "Visitor donates on website\n"
    "            ↓\n"
    "System sends automatic receipt email\n"
    "(amount · date · CECOME registration number)\n"
    "            ↓\n"
    "Donor Coordinator logs donation\n"
    "(name · amount · date · channel: M-Pesa/card/PayPal)\n"
    "            ↓\n"
    "At 30 days: send impact update\n"
    "'Your gift helped 3 girls complete ARP this month'\n"
    "            ↓\n"
    "Monthly newsletter sent to all donors\n"
    "            ↓\n"
    "At 6 months: personal thank-you or impact report\n"
    "            ↓\n"
    "At year-end: annual impact summary → renewal ask"
)
para('Key rule: No donor should give and hear nothing. Every gift gets a receipt, an impact update, and a year-end summary.', bold=True)

# STEP 4
heading2('STEP 4 — Volunteer Management Flow')
code_block(
    "Visitor submits volunteer application form\n"
    "            ↓\n"
    "Volunteer Coordinator receives email notification\n"
    "            ↓\n"
    "Acknowledgement sent within 48 hours\n"
    "            ↓\n"
    "Application reviewed against current needs\n"
    "            ↓\n"
    "  ACCEPTED                  NOT NEEDED NOW\n"
    "      ↓                           ↓\n"
    "Onboarding email         'We'll keep your details\n"
    "(role · schedule           and reach out when a\n"
    " · contact)                need matches your skills'\n"
    "      ↓\n"
    "Volunteer placed in program\n"
    "      ↓\n"
    "Regular check-in (monthly)\n"
    "      ↓\n"
    "End of engagement: thank-you + testimonial request"
)

# STEP 5
heading2('STEP 5 — Beneficiary / Community Member Flow')
para('This is the most sensitive flow. It must be fast, safe, and private.')
code_block(
    "Person in need lands on 'Get Help' page\n"
    "            ↓\n"
    "Helpline number visible immediately — no scrolling\n"
    "            ↓\n"
    "          CALLS\n"
    "            ↓\n"
    "  Safe — CECOME            Urgent — referred to\n"
    "  staff responds           hospital · police · legal aid\n"
    "            ↓\n"
    "Referred to nearest safe space or program\n"
    "            ↓\n"
    "Case handled confidentially\n"
    "(never publicized without consent)\n"
    "            ↓\n"
    "Follow-up by program officer"
)
para('Key rule: No real name, photo, or identifiable detail of a beneficiary goes on the website without their explicit written consent.', bold=True)

# STEP 6
heading2('STEP 6 — Funder / Partner Engagement Flow')
code_block(
    "Funder visits website\n"
    "            ↓\n"
    "Reads programs → downloads annual report\n"
    "            ↓\n"
    "Fills 'Partner / Fund Us' inquiry form\n"
    "(org name · type of support · interest area · contact)\n"
    "            ↓\n"
    "CECOME responds within 48 hours\n"
    "            ↓\n"
    "Sends: org profile · program brief · budget summary\n"
    "            ↓\n"
    "Call or meeting scheduled\n"
    "            ↓\n"
    "MOU / grant agreement signed\n"
    "            ↓\n"
    "Partner logo added to website\n"
    "Regular reporting sent as agreed"
)

# STEP 7
heading2('STEP 7 — Media / Press Flow')
code_block(
    "Journalist finds CECOME online or via referral\n"
    "            ↓\n"
    "Goes to Media Room\n"
    "            ↓\n"
    "Downloads: fact sheet · org bio · photos\n"
    "            ↓\n"
    "Contacts spokesperson directly\n"
    "(dedicated email or phone — not general inbox)\n"
    "            ↓\n"
    "Interview or quote provided within 24 hours\n"
    "            ↓\n"
    "Article published → CECOME shares on website + social\n"
    "→ Builds credibility and discoverability"
)

# STEP 8
heading2('STEP 8 — Monthly Operations Rhythm')
add_table(
    ['Week', 'Action'],
    [
        ['Week 1', 'Collect content from field (story, photos, numbers)'],
        ['Week 2', 'Write and publish story or news post · share on social'],
        ['Week 3', 'Send monthly newsletter to donor and supporter list'],
        ['Week 4', 'Review website analytics · respond to any pending forms'],
    ]
)

para('Every quarter:')
bullet('Publish program update / brief report')
bullet('Review and update impact numbers on homepage')
bullet('Check all links and forms still work')

para('Every year:')
bullet('Upload annual report')
bullet('Update team page (new staff, departures)')
bullet('Update partner logos')
bullet('Review and refresh program pages')

# STEP 9
heading2('STEP 9 — Accountability Layer')
add_table(
    ['What', 'Where on Website', 'Why'],
    [
        ['NGO registration number', 'Footer + About page', 'Legal legitimacy'],
        ['Annual reports', 'Our Impact → Reports', 'Funder and donor trust'],
        ['Financial summary', 'Financials & Governance', 'Transparency'],
        ['Partner / funder list', 'About → Partners', 'Credibility'],
        ['Data protection notice', 'Footer', 'Beneficiary safety + legal'],
        ['Last updated date on reports', 'Reports page', 'Shows currency of information'],
    ]
)

# STEP 10
heading2('STEP 10 — What Breaks Without This Model')
add_table(
    ['If This Is Skipped', 'What Happens'],
    [
        ['No assigned roles', 'Website goes stale — no updates, no stories'],
        ['No content rhythm', 'Funders assume org is inactive'],
        ['No donor follow-up', 'One-time donors never return'],
        ['No beneficiary privacy rule', 'Trust destroyed, legal risk'],
        ['No annual report', 'Serious funders walk away'],
        ['No media contact', 'Press coverage opportunity lost'],
        ['No analytics review', 'No way to know if the site is working'],
    ]
)

para(
    'The concept (what it contains) and the operations model (how it runs) together are the full brief before any building begins.',
    italic=True
)

doc.save('/home/user/bildfie/CECOME_Website_Concept_and_Operations.docx')
print('Done')
