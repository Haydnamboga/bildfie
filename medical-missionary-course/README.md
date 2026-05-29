# Medical Missionary Training — Course Content

Rich, self-contained lesson documents for the **Medical Missionary Training**
course, sourced entirely from **the Bible (KJV)** and the **Spirit of Prophecy**
(Ellen G. White), in the tradition of Weimar/NEWSTART, Wildwood, Uchee Pines,
Hartland, and Amazing Facts / Amazing Discoveries.

Each lesson is generated as its own Word document (`.docx`) so it can be
uploaded lesson-by-lesson into FlexAcademy or any LMS.

## Layout
```
medical-missionary-course/
├── mmt_builder.py     # the .docx rendering engine (styles, blocks, layout)
├── build.py           # compiles lessons/*.py → output/*.docx
├── OUTLINE.md         # full course outline + build checklist
├── lessons/           # one lesson_M_L.py per lesson (content as a Python dict)
│   └── lesson_1_1.py
└── output/            # generated .docx files (the deliverables)
```

## How each lesson is structured
Cover/title block → **Lesson at a Glance** (objectives, key texts, key SOP
sources, study time) → **Memory Verse** → richly formatted body (headings,
Scripture blocks, Ellen White quote blocks, callouts, figures, tables, lists) →
**Lesson Summary** → **Review & Reflection** questions → **Practical Assignment**
→ **References & Further Study**.

## Build
```bash
pip install python-docx          # one-time
cd medical-missionary-course
python build.py                  # build every lesson
python build.py 1_1 2_3          # build only specific lessons
```
Output `.docx` files land in `output/`.

## Authoring a new lesson
Copy `lessons/lesson_1_1.py`, rename to `lesson_<module>_<lesson>.py`, set
`FILENAME`, and fill in the `LESSON` dict. Supported body block types:
`h2, h3, p, ul, ol, scripture, quote, callout, figure, table, note, hr`.
Inline `**bold**` and `*italic*` are honoured in any text.

## Sources & accuracy
All Ellen G. White citations can be read, searched, and verified for free at
**egwwritings.org** (Ellen G. White Estate). Scripture is King James Version,
available at **biblegateway.com**. Citations are given conservatively (book,
chapter, and standard page where applicable); pagination may vary slightly
between print editions.
