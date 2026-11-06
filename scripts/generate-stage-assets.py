#!/usr/bin/env python3
"""Generate the guest-stage artwork: scene backdrops, the assistant cut-outs and her avatar.

    python scripts/generate-stage-assets.py

Output (all plain SVG, rewritten on every run):
  public/images/scenes/*.svg          1920x1080 backdrops, one per scene
  public/images/character/*.svg       800x1200 cut-outs, cut flat at the bottom edge
  public/images/assistant/avatar.svg  480x480 round-crop portrait

The stage shows a backdrop in a tall pane (roughly 40% of the screen wide) with
object-fit: cover, so every scene keeps its subject near the horizontal centre
and lets the sides carry only ambience. Palette: kraft paper, indigo ink and
marigold, matching resources/css/stage/tokens.css.
"""
from __future__ import annotations

import pathlib

ROOT = pathlib.Path(__file__).resolve().parent.parent / "public" / "images"

KRAFT = "#f6efe2"
KRAFT_2 = "#ecdfc5"
KRAFT_3 = "#d9c4a0"
KRAFT_4 = "#b99d70"
INK = "#1d2347"
INK_2 = "#2d3670"
INK_3 = "#4a548f"
MARIGOLD = "#e8a317"
MARIGOLD_2 = "#f6cc66"
TERRA = "#c4573a"
SAGE = "#5c8a5a"
SAGE_2 = "#86ad7e"
WOOD = "#a9794a"
WOOD_2 = "#8a5f38"
SKIN = "#f1c9a5"
SKIN_2 = "#e2b08a"
HAIR = "#2b1f1a"
WHITE = "#fffaf0"


def el(tag: str, **a: object) -> str:
    attrs = " ".join(f'{k.replace("_", "-")}="{v}"' for k, v in a.items() if v is not None)
    return f"<{tag} {attrs}/>"


def rect(x, y, w, h, fill, rx=0, **a):
    return el("rect", x=x, y=y, width=w, height=h, rx=rx, fill=fill, **a)


def circle(cx, cy, r, fill, **a):
    return el("circle", cx=cx, cy=cy, r=r, fill=fill, **a)


def ellipse(cx, cy, rx, ry, fill, **a):
    return el("ellipse", cx=cx, cy=cy, rx=rx, ry=ry, fill=fill, **a)


def path(d, fill="none", **a):
    return el("path", d=d, fill=fill, **a)


def line(x1, y1, x2, y2, stroke, w=6, **a):
    return el("line", x1=x1, y1=y1, x2=x2, y2=y2, stroke=stroke, stroke_width=w, stroke_linecap="round", **a)


def svg(w: int, h: int, body: list[str], defs: str = "") -> str:
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}">'
        f"<defs>{defs}</defs>{''.join(body)}</svg>\n"
    )


def gradient(gid: str, top: str, bottom: str) -> str:
    return (
        f'<linearGradient id="{gid}" x1="0" y1="0" x2="0" y2="1">'
        f'<stop offset="0" stop-color="{top}"/><stop offset="1" stop-color="{bottom}"/></linearGradient>'
    )


# --------------------------------------------------------------------------- props

def plant(x: int, y: int, s: float = 1.0) -> list[str]:
    """A potted plant standing on y."""
    leaves = []
    for dx, dy, r in ((-34, -150, 48), (30, -170, 54), (-6, -218, 44), (58, -110, 38), (-62, -102, 36)):
        leaves.append(ellipse(x + dx * s, y + dy * s, r * s * 0.55, r * s, SAGE, transform=f"rotate({dx} {x + dx * s} {y + dy * s})"))
    leaves.append(ellipse(x + 6 * s, y - 150 * s, 22 * s, 66 * s, SAGE_2))
    pot = path(f"M{x - 52 * s} {y - 70 * s} L{x + 52 * s} {y - 70 * s} L{x + 38 * s} {y} L{x - 38 * s} {y} Z", TERRA)
    return leaves + [pot, rect(x - 56 * s, y - 84 * s, 112 * s, 18 * s, "#a8442b", rx=6 * s)]


def box(x: int, y: int, w: int, h: int, fill: str, band: str = MARIGOLD) -> list[str]:
    return [rect(x, y, w, h, fill, rx=6), rect(x, y + h * 0.38, w, h * 0.14, band), rect(x + w * 0.42, y, w * 0.16, h * 0.38, "#00000014")]


def jar(x: int, y: int, w: int, h: int, fill: str) -> list[str]:
    return [rect(x, y, w, h, fill, rx=10), rect(x + 6, y - 14, w - 12, 18, INK, rx=5), rect(x + 10, y + h * 0.3, w - 20, h * 0.3, WHITE, rx=4)]


def bag(x: int, y: int, w: int, h: int, fill: str) -> list[str]:
    return [path(f"M{x} {y + 14} L{x + w} {y + 14} L{x + w - 6} {y + h} L{x + 6} {y + h} Z", fill),
            path(f"M{x + w * 0.3} {y + 14} Q{x + w * 0.5} {y - 20} {x + w * 0.7} {y + 14}", "none", stroke=INK, stroke_width=5)]


def shelf(x: int, y: int, w: int, items: list[str]) -> list[str]:
    return items + [rect(x, y, w, 16, WOOD, rx=4), rect(x, y + 16, w, 7, WOOD_2)]


def frame(x: int, y: int, w: int, h: int, art: list[str], fill: str = WHITE) -> list[str]:
    return [rect(x - 12, y - 12, w + 24, h + 24, INK, rx=6), rect(x, y, w, h, fill)] + art


def clock(cx: int, cy: int, r: int) -> list[str]:
    return [circle(cx, cy, r + 10, INK), circle(cx, cy, r, WHITE), line(cx, cy, cx, cy - r * 0.68, INK, 7), line(cx, cy, cx + r * 0.5, cy + r * 0.2, INK, 7), circle(cx, cy, 8, MARIGOLD)]


def lamp(x: int, y: int) -> list[str]:
    return [line(x, y, x + 50, y - 120, INK, 8), line(x + 50, y - 120, x + 130, y - 150, INK, 8),
            path(f"M{x + 100} {y - 168} L{x + 170} {y - 140} L{x + 140} {y - 100} Z", MARIGOLD), ellipse(x, y + 6, 46, 12, INK)]


def monitor(x: int, y: int, w: int, h: int) -> list[str]:
    bars = [rect(x + 24, y + 30 + i * 34, w * (0.5 + 0.12 * ((i * 3) % 4) / 3), 14, MARIGOLD if i == 0 else INK_3, rx=7) for i in range(4)]
    return [rect(x, y, w, h, INK, rx=14), rect(x + 12, y + 12, w - 24, h - 24, KRAFT, rx=6)] + bars + [
        rect(x + w / 2 - 14, y + h, 28, 46, INK_2), rect(x + w / 2 - 70, y + h + 40, 140, 14, INK_2, rx=7)]


def floor(y: int, w: int = 1920, h: int = 1080) -> list[str]:
    boards = [rect(0, y, w, h - y, WOOD, rx=0), rect(0, y, w, 10, WOOD_2)]
    for i in range(1, 9):
        boards.append(line(0, y + i * (h - y) / 9, w, y + i * (h - y) / 9, WOOD_2, 3, opacity=".35"))
    return boards


# --------------------------------------------------------------------------- scenes

def wall(defs_id: str = "wall", top: str = KRAFT, bottom: str = KRAFT_2) -> tuple[str, list[str]]:
    return gradient(defs_id, top, bottom), [rect(0, 0, 1920, 1080, f"url(#{defs_id})")]


def scene_storefront() -> str:
    d, b = wall("wall", "#f9f2e2", KRAFT_3)
    b += [rect(0, 0, 1920, 270, INK)]
    # awning
    for i in range(16):
        b.append(path(f"M{i * 120} 190 L{i * 120 + 120} 190 L{i * 120 + 120} 300 Q{i * 120 + 60} 340 {i * 120} 300 Z", MARIGOLD if i % 2 == 0 else WHITE))
    b += [rect(0, 170, 1920, 26, INK_2)]
    # sign
    b += [rect(690, 38, 540, 104, MARIGOLD, rx=22), rect(702, 50, 516, 80, "none", rx=14, stroke=INK, stroke_width=6),
          rect(790, 74, 340, 14, INK, rx=7), rect(850, 100, 220, 12, INK_2, rx=6)]
    # shop window left & right of the door
    for x0 in (120, 1200):
        b += [rect(x0, 400, 600, 440, "#cfe3ea", rx=16), rect(x0, 400, 600, 440, "none", rx=16, stroke=INK, stroke_width=14),
              line(x0 + 300, 400, x0 + 300, 840, INK, 10), path(f"M{x0 + 30} 470 L{x0 + 150} 410 L{x0 + 110} 560 Z", "#ffffff66")]
        b += shelf(x0 + 30, 640, 240, box(x0 + 50, 560, 70, 80, TERRA) + jar(x0 + 150, 560, 60, 80, MARIGOLD_2))
        b += shelf(x0 + 330, 640, 240, bag(x0 + 360, 570, 70, 70, SAGE) + box(x0 + 470, 560, 70, 80, INK_3))
        b += shelf(x0 + 30, 780, 540, jar(x0 + 60, 710, 60, 70, TERRA) + box(x0 + 200, 700, 90, 80, MARIGOLD) + bag(x0 + 400, 710, 70, 70, INK_2))
    # door
    b += [rect(780, 380, 360, 560, INK_2, rx=18), rect(810, 410, 300, 300, "#cfe3ea", rx=10), circle(1090, 700, 14, MARIGOLD),
          rect(850, 470, 220, 70, WHITE, rx=12), rect(870, 490, 120, 12, INK, rx=6), rect(870, 512, 80, 10, INK_3, rx=5)]
    b += floor(940)
    b += [rect(740, 940, 440, 24, KRAFT_4, rx=6)]
    b += plant(90, 940, 1.15) + plant(1830, 940, 1.15)
    b += [circle(960, 330, 6, INK)]
    return svg(1920, 1080, b, d)


def scene_catalog() -> str:
    d, b = wall("wall", "#f7efdf", KRAFT_2)
    b += [rect(0, 0, 1920, 1080, "none")]
    # three wide shelf rows
    rows = [(250, TERRA), (500, INK_3), (750, SAGE)]
    for y, accent in rows:
        b += [rect(80, y - 190, 1760, 230, "#00000010", rx=0)]
        x = 120
        for i in range(10):
            kind = (i + y // 250) % 3
            if kind == 0:
                b += box(x, y - 120, 110, 120, accent, MARIGOLD)
            elif kind == 1:
                b += jar(x + 10, y - 110, 84, 110, MARIGOLD_2 if i % 2 else KRAFT_3)
            else:
                b += bag(x + 6, y - 112, 96, 112, INK_2 if i % 2 else accent)
            x += 170
        b += [rect(60, y, 1800, 18, WOOD, rx=5), rect(60, y + 18, 1800, 8, WOOD_2)]
    b += [rect(60, 40, 40, 1040, WOOD_2), rect(1820, 40, 40, 1040, WOOD_2)]
    # price tags
    for i, x in enumerate(range(170, 1800, 340)):
        for y, _ in rows:
            b += [rect(x, y + 36, 110, 38, WHITE, rx=6), rect(x + 14, y + 50, 56, 8, INK, rx=4), circle(x + 90, y + 55, 7, MARIGOLD)]
    b += floor(900)
    b += [rect(0, 820, 1920, 80, KRAFT_3)]
    b += [rect(560, 770, 800, 60, INK_2, rx=10), rect(580, 740, 760, 40, KRAFT, rx=8)]
    return svg(1920, 1080, b, d)


def scene_workspace() -> str:
    d, b = wall("wall", "#f6eddc", KRAFT_3)
    # pegboard
    b += [rect(160, 80, 560, 420, KRAFT_3, rx=14)]
    for gx in range(190, 700, 40):
        for gy in range(110, 480, 40):
            b.append(circle(gx, gy, 3, KRAFT_4))
    b += [line(220, 170, 220, 280, INK, 8), rect(205, 280, 30, 90, INK_3, rx=6), line(330, 180, 420, 180, INK, 8), ellipse(430, 230, 70, 46, MARIGOLD),
          rect(520, 140, 130, 130, TERRA, rx=14), rect(540, 160, 90, 12, WHITE, rx=6), rect(540, 184, 60, 12, WHITE, rx=6)]
    # window
    b += [rect(1180, 90, 540, 470, "#cfe3ea", rx=14), rect(1180, 90, 540, 470, "none", rx=14, stroke=INK, stroke_width=14),
          line(1450, 90, 1450, 560, INK, 10), line(1180, 325, 1720, 325, INK, 10), ellipse(1560, 200, 70, 28, "#ffffff99"), ellipse(1300, 240, 50, 22, "#ffffff99")]
    b += frame(840, 130, 220, 160, [rect(870, 170, 160, 18, MARIGOLD, rx=9), rect(870, 206, 110, 14, INK_3, rx=7), rect(870, 238, 130, 14, INK_3, rx=7)])
    b += floor(860)
    # desk
    b += [rect(380, 650, 1160, 34, WOOD, rx=8), rect(380, 684, 1160, 16, WOOD_2), rect(430, 700, 34, 280, WOOD_2), rect(1456, 700, 34, 280, WOOD_2)]
    b += monitor(720, 400, 470, 230)
    b += lamp(500, 650)
    b += [rect(1260, 600, 120, 50, TERRA, rx=10), rect(1280, 570, 80, 36, MARIGOLD_2, rx=8)]
    b += [rect(560, 628, 130, 22, WHITE, rx=6), rect(580, 606, 100, 22, MARIGOLD, rx=6)]
    b += plant(1700, 860, 1.2) + plant(180, 860, 0.9)
    return svg(1920, 1080, b, d)


def scene_studio() -> str:
    d, b = wall("wall", "#f2e7d1", KRAFT_3)
    # paper backdrop roll
    b += [rect(420, 0, 1080, 780, MARIGOLD_2), rect(420, 0, 1080, 780, "url(#fade)"), rect(380, 40, 1160, 40, INK, rx=20)]
    b += [path("M420 760 Q960 880 1500 760 L1500 900 L420 900 Z", MARIGOLD_2)]
    # soft boxes
    for x, flip in ((130, 1), (1640, -1)):
        b += [line(x + 70 * flip, 420, x + 70 * flip, 940, INK, 10), rect(x - 40 * flip if flip > 0 else x - 100, 230, 140, 200, WHITE, rx=14),
              rect(x - 28 * flip if flip > 0 else x - 88, 244, 116, 172, KRAFT_2, rx=8)]
        b += [line(x + 70 * flip, 940, x - 20 * flip, 1010, INK, 10), line(x + 70 * flip, 940, x + 160 * flip, 1010, INK, 10)]
    # camera tripod
    b += [line(960, 880, 860, 1040, INK, 10), line(960, 880, 1060, 1040, INK, 10), line(960, 880, 960, 1030, INK, 10),
          rect(900, 800, 120, 82, INK_2, rx=12), circle(960, 842, 28, INK), circle(960, 842, 14, "#9ec4d4")]
    b += floor(930)
    b += [ellipse(960, 960, 420, 36, "#00000022")]
    b += plant(1500, 940, 0.8)
    extra = '<linearGradient id="fade" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff55"/><stop offset="1" stop-color="#00000000"/></linearGradient>'
    return svg(1920, 1080, b, d + extra)


def scene_office() -> str:
    d, b = wall("wall", "#f4ead6", KRAFT_3)
    b += [rect(0, 760, 1920, 320, WOOD)]
    # notice board
    b += [rect(160, 120, 640, 420, WOOD_2, rx=10), rect(180, 140, 600, 380, "#c9a574", rx=6)]
    for x, y, c, r in ((220, 180, WHITE, -4), (400, 200, MARIGOLD_2, 3), (560, 170, WHITE, 2), (250, 340, TERRA, -2), (450, 360, WHITE, 4), (610, 330, MARIGOLD_2, -3)):
        b += [rect(x, y, 140, 120, c, rx=6, transform=f"rotate({r} {x + 70} {y + 60})"), circle(x + 70, y + 12, 7, INK)]
        b += [rect(x + 20, y + 40, 100, 9, INK_3, rx=4, transform=f"rotate({r} {x + 70} {y + 60})"), rect(x + 20, y + 62, 70, 9, INK_3, rx=4, transform=f"rotate({r} {x + 70} {y + 60})")]
    b += clock(1000, 190, 80)
    b += frame(1230, 120, 260, 190, [rect(1260, 150, 200, 20, INK, rx=10), rect(1260, 190, 140, 14, INK_3, rx=7), circle(1420, 262, 26, MARIGOLD), path("M1408 262 l8 10 l18 -22", "none", stroke=WHITE, stroke_width=6)])
    b += frame(1560, 150, 160, 220, [rect(1585, 180, 110, 80, KRAFT_3), rect(1585, 280, 110, 14, INK_3, rx=7), rect(1585, 306, 80, 14, INK_3, rx=7)])
    # filing cabinet & counter
    b += [rect(1380, 480, 360, 560, INK_2, rx=10)]
    for i in range(4):
        b += [rect(1400, 500 + i * 134, 320, 118, INK_3, rx=8), rect(1510, 548 + i * 134, 100, 12, KRAFT, rx=6)]
    b += [rect(160, 640, 1000, 36, KRAFT_4, rx=8), rect(180, 676, 960, 330, WOOD_2)]
    b += [rect(300, 560, 230, 80, WHITE, rx=8), rect(320, 580, 130, 10, INK, rx=5), rect(320, 602, 90, 10, INK_3, rx=5)]
    b += [rect(660, 596, 90, 44, TERRA, rx=8), rect(640, 624, 130, 16, MARIGOLD, rx=6)]
    b += plant(1830, 1000, 1.0)
    return svg(1920, 1080, b, d)


def scene_team() -> str:
    d, b = wall("wall", "#f7f0e0", KRAFT_2)
    # whiteboard with sticky notes
    b += [rect(300, 90, 1320, 520, WHITE, rx=18), rect(300, 90, 1320, 520, "none", rx=18, stroke=INK, stroke_width=14)]
    cols = [MARIGOLD_2, "#f7b8a5", "#b9d9b0", "#bcc6ee"]
    for i in range(12):
        x = 360 + (i % 6) * 205
        y = 150 + (i // 6) * 190
        b += [rect(x, y, 150, 150, cols[(i * 5) % 4], rx=6, transform=f"rotate({[-3, 2, -1, 3, -2, 1][i % 6]} {x + 75} {y + 75})"),
              rect(x + 24, y + 38, 100, 10, INK_3, rx=5), rect(x + 24, y + 62, 70, 10, INK_3, rx=5)]
    b += [rect(840, 585, 240, 24, INK, rx=8)]
    b += floor(800)
    # long table
    b += [rect(260, 790, 1400, 34, WOOD, rx=8), rect(260, 824, 1400, 14, WOOD_2), rect(320, 838, 34, 200, WOOD_2), rect(1566, 838, 34, 200, WOOD_2)]
    for x, c in ((520, TERRA), (960, MARIGOLD), (1400, SAGE)):
        b += [rect(x - 90, 680, 180, 24, c, rx=10), rect(x - 78, 704, 156, 90, c, rx=12, opacity=".8")]
        b += [circle(x, 640, 38, SKIN), path(f"M{x - 42} 636 Q{x} 570 {x + 42} 636 Q{x} 606 {x - 42} 636 Z", HAIR)]
    b += [rect(840, 760, 240, 30, INK_2, rx=8), rect(870, 748, 180, 14, KRAFT_2, rx=6)]
    b += plant(150, 1000, 1.1) + plant(1790, 1000, 1.1)
    return svg(1920, 1080, b, d)


def scene_booking() -> str:
    d, b = wall("wall", "#f7efde", KRAFT_3)
    # big wall calendar
    b += [rect(600, 70, 720, 640, WHITE, rx=18), rect(600, 70, 720, 120, TERRA, rx=18), rect(600, 150, 720, 40, TERRA), rect(660, 40, 36, 70, INK, rx=12), rect(1224, 40, 36, 70, INK, rx=12),
          rect(700, 98, 300, 30, WHITE, rx=15, opacity=".9")]
    for r in range(5):
        for c in range(7):
            x = 636 + c * 96
            y = 220 + r * 86
            hot = (r, c) in ((1, 3), (2, 1), (3, 5))
            b += [rect(x, y, 78, 66, MARIGOLD if hot else KRAFT, rx=10)]
            b += [rect(x + 20, y + 24, 38, 10, INK if hot else KRAFT_4, rx=5)]
    b += [path("M1190 440 q14 18 30 -6", "none", stroke=INK, stroke_width=7), circle(1200, 456, 44, "none", stroke=TERRA, stroke_width=7)]
    b += clock(260, 210, 90) + clock(1660, 210, 70)
    b += floor(830)
    # reception desk
    b += [rect(420, 760, 1080, 40, KRAFT, rx=10), rect(420, 800, 1080, 230, INK_2, rx=8), rect(470, 830, 980, 14, MARIGOLD, rx=7)]
    b += [rect(560, 690, 280, 70, WHITE, rx=8), rect(580, 706, 160, 10, INK, rx=5), rect(580, 728, 110, 10, INK_3, rx=5)]
    b += [rect(1120, 660, 190, 100, INK, rx=12), rect(1130, 670, 170, 80, KRAFT, rx=6), rect(1148, 690, 100, 10, MARIGOLD, rx=5)]
    b += [circle(1400, 720, 30, MARIGOLD), rect(1388, 722, 24, 36, MARIGOLD)]
    b += plant(150, 1000, 1.1) + plant(1790, 1000, 1.1)
    return svg(1920, 1080, b, d)


# --------------------------------------------------------------------------- the assistant

def head() -> list[str]:
    return [
        path("M285 330 Q268 520 330 566 L470 566 Q532 520 515 330 Z", HAIR),
        rect(362, 430, 76, 130, SKIN_2, rx=20),
        ellipse(400, 335, 108, 128, SKIN),
        ellipse(292, 345, 16, 30, SKIN), ellipse(508, 345, 16, 30, SKIN),
        path("M292 330 Q290 200 400 196 Q510 200 508 330 Q480 250 420 238 Q350 262 292 330 Z", HAIR),
        path("M300 300 Q330 224 420 232", "none", stroke="#4a3328", stroke_width=6, opacity=".5", stroke_linecap="round"),
        ellipse(358, 346, 11, 14, HAIR), ellipse(442, 346, 11, 14, HAIR),
        circle(362, 341, 4, WHITE), circle(446, 341, 4, WHITE),
        path("M338 318 Q358 306 378 316", "none", stroke=HAIR, stroke_width=6, stroke_linecap="round"),
        path("M422 316 Q442 306 462 318", "none", stroke=HAIR, stroke_width=6, stroke_linecap="round"),
        path("M400 356 Q394 384 406 386", "none", stroke=SKIN_2, stroke_width=5, stroke_linecap="round"),
        path("M366 408 Q400 436 434 408", "none", stroke="#a24b3a", stroke_width=7, stroke_linecap="round"),
        ellipse(334, 386, 20, 11, "#ee9a86", opacity=".55"), ellipse(466, 386, 20, 11, "#ee9a86", opacity=".55"),
    ]


def torso() -> list[str]:
    return [
        path("M130 1200 L130 720 Q130 570 290 535 L510 535 Q670 570 670 720 L670 1200 Z", INK_2),
        path("M130 1200 L130 720 Q130 570 290 535 L336 535 Q250 600 250 760 L250 1200 Z", INK, opacity=".35"),
        path("M322 535 L400 720 L478 535 Z", WHITE),
        path("M322 535 L400 720 L356 790 L284 566 Z", INK),
        path("M478 535 L400 720 L444 790 L516 566 Z", INK),
        path("M312 548 Q400 626 488 548 L474 590 Q400 660 326 590 Z", MARIGOLD),
        path("M392 626 L360 700 L404 674 L440 702 L416 626 Z", "#c98512"),
        rect(520, 770, 80, 54, WHITE, rx=8), rect(532, 784, 30, 8, INK, rx=4), rect(532, 800, 54, 8, INK_3, rx=4), circle(586, 788, 6, MARIGOLD),
    ]


def arm(d: str) -> str:
    return path(d, "none", stroke=INK_2, stroke_width=92, stroke_linecap="round")


def character(pose: str) -> str:
    b: list[str] = []
    if pose == "greeting":
        b += [arm("M610 700 Q650 900 600 1210"), arm("M190 700 Q110 640 128 500")]
        b += torso()
        b += [circle(128, 462, 52, SKIN), ellipse(128, 410, 14, 30, SKIN), ellipse(94, 424, 12, 26, SKIN), ellipse(160, 424, 12, 26, SKIN)]
    elif pose == "clasped":
        b += torso()
        b += [arm("M185 700 Q190 900 330 950"), arm("M615 700 Q610 900 470 950"), circle(350, 950, 52, SKIN), circle(450, 950, 52, SKIN_2)]
    else:  # notepad
        b += [arm("M185 700 Q150 900 290 1000")]
        b += torso()
        b += [arm("M615 700 Q650 900 520 940"), rect(300, 860, 230, 300, INK, rx=26, transform="rotate(-8 415 1010)"), rect(318, 880, 194, 262, KRAFT, rx=14, transform="rotate(-8 415 1010)"),
              rect(346, 920, 120, 14, MARIGOLD, rx=7, transform="rotate(-8 415 1010)"), rect(346, 956, 140, 12, INK_3, rx=6, transform="rotate(-8 415 1010)"),
              rect(346, 988, 100, 12, INK_3, rx=6, transform="rotate(-8 415 1010)"), circle(510, 930, 50, SKIN), circle(300, 1004, 46, SKIN_2)]
    b += head()
    clip = '<clipPath id="c"><rect x="0" y="0" width="800" height="1200"/></clipPath>'
    return svg(800, 1200, [f'<g clip-path="url(#c)">'] + b + ["</g>"], clip)


def avatar() -> str:
    d = gradient("g", "#6f7bc4", INK_2)
    b = [rect(0, 0, 480, 480, "url(#g)"), circle(240, 440, 220, MARIGOLD_2, opacity=".25"), '<g transform="translate(-160 -108)">'] + torso() + head() + ["</g>"]
    return svg(480, 480, b, d)


def write(rel: str, content: str) -> None:
    target = ROOT / rel
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(content, encoding="utf-8", newline="\n")
    print(f"wrote {rel} ({len(content):,} bytes)")


def main() -> None:
    scenes = {
        "storefront": scene_storefront, "catalog": scene_catalog, "workspace": scene_workspace, "studio": scene_studio,
        "office": scene_office, "team": scene_team, "booking": scene_booking,
    }
    for name, fn in scenes.items():
        write(f"scenes/{name}.svg", fn())
    for pose in ("greeting", "clasped", "notepad"):
        write(f"character/assistant-{pose}.svg", character(pose))
    write("assistant/avatar.svg", avatar())


if __name__ == "__main__":
    main()
