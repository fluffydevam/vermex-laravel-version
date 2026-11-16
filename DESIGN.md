---
version: "alpha"
name: "Vermex Pest Solutions — Admin Shell"
description: "Minimalista Funcional B2B design system adapted for Vermex Pest Solutions transaction processing & inventory system. Vermex crimson brand identity with clean, professional, minimalist UI."
colors:
  primary: "#d32f2f"
  primary-hover: "#e53935"
  surface: "#ffffff"
  surface-muted: "#f8f8f8"
  surface-subtle: "#fdf2f2"
  border: "#e5e7eb"
  border-subtle: "#fecaca"
  ink: "#212529"
  ink-muted: "#6c757d"
  sidebar: "#2b110d"
  sidebar-hover: "#3d1712"
  sidebar-active: "#431913"
  sidebar-text: "#c5b8b5"
  sidebar-accent: "#ff6b5a"
  success: "#28a745"
  warning: "#ffc107"
  danger: "#dc3545"
  link: "#d32f2f"
  link-hover: "#b71c1c"
dark_colors:
  surface: "#1a1210"
  surface-muted: "#100b0a"
  border: "#3a2925"
  ink: "#f4edeb"
  ink-muted: "#b0a19d"
  link: "#ff8a7a"
  link-hover: "#ffb4aa"
  sidebar: "#22100c"
typography:
  display:
    fontFamily: "Inter"
    fontSize: "2.5rem"
    fontWeight: "700"
  h1:
    fontFamily: "Inter"
    fontSize: "2.25rem"
    fontWeight: "600"
  h2:
    fontFamily: "Inter"
    fontSize: "1.5rem"
    fontWeight: "600"
  body-md:
    fontFamily: "Inter"
    fontSize: "1rem"
    fontWeight: "400"
  label:
    fontFamily: "Inter"
    fontSize: "0.875rem"
    fontWeight: "500"
  mono:
    fontFamily: "JetBrains Mono"
    fontSize: "0.875rem"
    fontWeight: "400"
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
spacing:
  unit: "0.5rem"
  sm: "0.5rem"
  md: "1rem"
  lg: "1.5rem"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.sm}"
    padding: "12px"
  button-ghost:
    backgroundColor: "transparent"
    border: "1px solid {colors.border}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
  card:
    backgroundColor: "{colors.surface}"
    border: "1px solid {colors.border}"
    rounded: "{rounded.md}"
    shadow: "0 2px 8px rgba(0,0,0,0.08)"
  input:
    border: "1px solid {colors.border}"
    rounded: "{rounded.sm}"
    focusRing: "2px solid {colors.primary}"
sidebar:
  width: "16rem"
  background: "{colors.sidebar}"
  activeBorder: "4px solid {colors.primary-hover}"
---

## Overview

Minimalista Funcional B2B for Vermex Pest Solutions — a transaction processing, client/contract, dispatch, inspection, inventory, and billing system used by staff eight hours a day. Cognitive load is the enemy; every pixel earns its place through utility. Color is semantic, never decorative. Typography carries hierarchy alone. Adapted from Minimalismo Funcional B2B with the Vermex crimson brand identity retained as the primary action color.

## When to Use

Internal operational tools where users live inside the product all day: dashboards, data tables, forms, modals, printable reports. Fails for consumer-facing marketing sites that need emotional pull.

## Design Principles

- **Content density over decoration** — information is the interface, chrome is debt
- **Semantic color only** — every hue signals state (active, warning, destructive), never mood
- **Typography as architecture** — weight and size do all hierarchy work that borders and boxes used to do
- **Keyboard-first, mouse-tolerant** — power users set the interaction baseline
- **Silence as default** — empty states, muted palettes, restrained motion

## Colors

- **Vermex Crimson** (#d32f2f) — Primary actions, active states, brand
- **Crimson Hover** (#e53935) — Hover states for primary actions
- **Surface** (#FFFFFF) — Card backgrounds, primary surface
- **Surface Muted** (#F8F8F8) — Secondary surfaces, section backgrounds
- **Surface Subtle** (#FDF2F2) — Vermex-tinted backgrounds, muted accents
- **Border** (#E5E7EB) — 1px strokes between surfaces
- **Border Subtle** (#FECACA) — Vermex-tinted borders
- **Ink** (#212529) — Primary text, dark surface
- **Ink Muted** (#6C757D) — Secondary text, captions
- **Sidebar** (#2B110D) — Dark Vermex sidebar background
- **Sidebar Hover** (#3D1712) — Sidebar item hover
- **Sidebar Active** (#431913) — Sidebar active item
- **Sidebar Text** (#C5B8B5) — Sidebar muted text
- **Sidebar Accent** (#FF6B5A) — Sidebar active icon color
- **Success** (#28A745) — Positive states
- **Warning** (#FFC107) — Attention states
- **Danger** (#DC3545) — Error / destructive states
- **Link** (#D32F2F, dark #FF8A7A) — Inline links and link hovers; kept separate from solid crimson fills so text contrast holds in both themes

## Dark Mode

Dark mode is **class-based**: the pre-paint script in `partials/theme-script.blade.php` adds `.dark` to `<html>` before the first paint. On a first visit it follows `prefers-color-scheme`; once the operator uses the header (or login) sun/moon toggle, the choice is stored in `localStorage` under `vermex-theme` and wins over the OS setting. While no explicit choice exists, an OS theme change is followed live.

The theme is implemented by **swapping semantic tokens**, not by adding `dark:` classes to pages. Tailwind utilities compile to `var(--color-…)`, so `.dark` in `resources/css/app.css` re-themes the shell in one place. Only the state components (`alert`, `badge`, `stat-card`, `button`) need purpose-built soft tokens because they previously used default Tailwind palettes.

| Token | Light | Dark |
|---|---|---|
| `surface` | `#ffffff` | `#1a1210` |
| `surface-muted` | `#f8f8f8` | `#100b0a` |
| `border` | `#e5e7eb` | `#3a2925` |
| `ink` | `#212529` | `#f4edeb` |
| `ink-muted` | `#6c757d` | `#b0a19d` |
| `link` | `#d32f2f` | `#ff8a7a` |
| `sidebar` | `#2b110d` | `#22100c` |

Contrast rules:
- Solid primary buttons keep `brand-600`/`brand-700` with white text (~5:1); the brand scale is not inverted for dark mode.
- Text links and focus accents use `link` / `link-hover`, which lighten in dark mode to stay above 4.5:1.
- State surfaces use `*-soft`, `*-soft-border`, and `*-ink` / `*-strong` pairs, each with a dark set.

**Print and PDF are always light.** `@media print` resets all overridden tokens, and `partials/pdf.blade.php` temporarily adds `.theme-light` to the captured element before `html2pdf` runs. The standalone `layouts/print.blade.php` also carries `.theme-light`.

## Typography

- **Display / H1:** Inter — Weight 700 / 600, tight tracking, headline impact
- **Body:** Inter — Weight 400, 1rem / 1.6 line-height, max 72ch per line
- **UI Labels / Captions:** Inter — 0.875rem, weight 500, slight letter-spacing
- **Monospace:** JetBrains Mono — IDs, amounts, dates, codes

Scale:
- Hero/Display: clamp(2.5rem, 5vw, 4rem)
- H1: 2.25rem
- H2: 1.5rem
- Body: 1rem / 1.6
- Small: 0.875rem

## Layout

- **Grid:** CSS Grid primary. Max-width containment: 1280px centered with 1.5rem side padding.
- **Spacing rhythm:** Balanced. Base unit: 0.5rem (8px).
- **Section vertical gaps:** clamp(4rem, 8vw, 8rem).
- **Mobile collapse:** All multi-column layouts collapse below 768px. No horizontal overflow.
- **z-index contract:** base (0) / sticky-nav (100) / overlay (200) / modal (300) / toast (500).
- **No `h-screen`** — use `min-h-[100dvh]`

## Elevation & Depth

Abundant white space, clean sans-serif typography, minimalist icons, subtle micro-interactions, soft shadows, fluid transitions, focus on readability and clarity.

- **Physics:** Ease-out curves, 200–300ms duration.
- **Entry animations:** Fade + translate-Y (16px → 0) over 420ms ease-out. Staggered cascades for lists: 80ms between items.
- **Hover states:** Subtle color shift + shadow adjustment over 200ms.
- **Page transitions:** Fade only (200ms).
- **Performance:** Only transform and opacity animated. No layout-triggering properties.
- **Shadows:** Max `0 2px 8px rgba(0,0,0,0.08)`.

## Shapes

Base corner radius: 4px. Full scale: sm 4px / md 8px / lg 12px.

## Components

- **Primary Button:** Rounded (4px). Vermex crimson fill. Hover: 8% darken + subtle lift shadow. Active: -1px translate tactile press. Font weight 600. No outer glows.
- **Secondary / Ghost Button:** Outline variant. 1px border in muted color. Text in ink color. Hover: subtle background fill.
- **Cards:** Rounded (8px). Surface background. Subtle shadow (0 2px 8px rgba(0,0,0,0.06)). 1px border stroke.
- **Stat Cards:** Vermex-subtle background (#fdf2f2), crimson text/icon, 1px border.
- **Inputs:** Label above input. 1px border stroke. Focus ring: 2px crimson offset 2px. Error text below in semantic danger red. No floating labels.
- **Navigation:** Primary surface background. Active item: crimson indicator. Font weight 500 when active.
- **Badges:** Semantic colors (success/warning/danger), small rounded pills, 10px text.
- **Tables:** 1px borders, ink-muted header text, uppercase 0.75rem, zebra rows on surface-muted.
- **Modals:** Surface background, overlay backdrop-filter blur, rounded 12px, 200ms fade.
- **Empty States:** Icon-based composition with descriptive text and primary action button.
- **Pagination:** Surface buttons, crimson active state.

## Do's and Don'ts

- No emojis in UI — use icon system only (Lucide)
- No decorative gradients — flat color only
- No shadows heavier than `0 2px 8px rgba(0,0,0,0.08)`
- No pure black (#000000) — use off-black or charcoal (#212529)
- No oversaturated accent colors (saturation cap: 80%)
- No 3-column equal-width feature layouts — use asymmetric grid
- No `h-screen` — use `min-h-[100dvh]`
- No AI copywriting clichés: "Elevate", "Seamless", "Unleash", "Next-Gen"
- No broken external image links — use inline SVG
- No generic lorem ipsum in demos

- Do abundant white space
- Do clean sans-serif typography
- Do minimalist icons
- Do subtle micro-interactions
- Do soft shadows
- Do focus on readability and clarity

## Use Case

Admin dashboard, internal operational tool, B2B software
