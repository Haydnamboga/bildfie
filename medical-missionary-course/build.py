"""
build.py — compile every lesson_*.py in lessons/ into output/<filename>.docx

Each lesson module must expose a top-level dict named LESSON and a string FILENAME.
Run:  python build.py            # build all lessons
      python build.py 1_1 2_3    # build only matching lessons
"""

import importlib.util
import sys
from pathlib import Path

from mmt_builder import build_lesson

ROOT = Path(__file__).resolve().parent
LESSONS_DIR = ROOT / "lessons"
OUTPUT_DIR = ROOT / "output"


def load_lesson(path):
    spec = importlib.util.spec_from_file_location(path.stem, path)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def main(argv):
    OUTPUT_DIR.mkdir(exist_ok=True)
    wanted = set(argv)
    files = sorted(LESSONS_DIR.glob("lesson_*.py"))
    if not files:
        print("No lessons found in", LESSONS_DIR)
        return
    built = 0
    for path in files:
        key = path.stem.replace("lesson_", "")
        if wanted and key not in wanted:
            continue
        mod = load_lesson(path)
        if not hasattr(mod, "LESSON") or not hasattr(mod, "FILENAME"):
            print(f"  skip {path.name} (missing LESSON/FILENAME)")
            continue
        out = OUTPUT_DIR / mod.FILENAME
        build_lesson(mod.LESSON, str(out))
        print(f"  built {out.name}")
        built += 1
    print(f"\nDone. {built} lesson document(s) written to {OUTPUT_DIR}")


if __name__ == "__main__":
    main(sys.argv[1:])
