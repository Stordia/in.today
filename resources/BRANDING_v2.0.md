# in.today – Brand Guide v2.0

_Last updated: 2025-11-27_

## 1. Brand Essence

**in.today** is a global platform that helps people discover where to eat **today** and helps restaurants turn visibility into **real reservations**.

- **B2B:** independent restaurants, small groups, boutique hospitality brands  
- **B2C:** guests who want a great place _tonight_, with minimal friction  
- **Positioning:** modern, trustworthy, European, restaurant‑first  
- **Keywords:** reliable, premium, calm, precise, “today”, hospitality

Working taglines:

- “Know what’s in.today.”  
- “Turn tonight’s empty tables into guests.”  

---

## 2. Logo & Icon

### 2.1 Concept

The logo is built around a simple, memorable icon:

- A rounded **diamond** shape → table / floor plan / location area  
- A single **“today dot”** on the top edge → something happening _now_  
- Clean, geometric, flat → works in product UI and marketing

Wordmark: **in.today** (all lowercase)  
- “in” = being in the scene / in the know  
- “today” = focus on today / tonight, not abstract future

### 2.2 Primary Icon (App / Favicon)

Shape guidelines (not pixel‑perfect rules):

- Canvas: square (e.g. 256×256)  
- Outer diamond: ~65% of canvas, rounded corners  
- Optional inner diamond cut‑out: ~50% of outer  
- Dot diameter: ~22–25% of canvas, centered at the top edge, overlapping slightly

Coloring (primary icon on light background):

- Outer diamond: **Service Blue** `#1D4ED8`  
- Inner diamond: **Ink** `#020617` or transparent (for cut‑out)  
- Today dot: **Today Signal** `#22C55E`  

Dark‑mode version:

- Keep shapes identical  
- Outer diamond: `#60A5FA` (lighter blue)  
- Inner diamond: `#020617` (or transparent)  
- Today dot: `#4ADE80`  

### 2.3 Logo Variants

1. **Icon only**  
   - File: `public/brand/in-today-logo-icon.svg`  
   - Uses: favicon, app icon base, avatar, tiny placements.

2. **Horizontal logo (icon + wordmark)**  
   - File: `public/brand/in-today-logo-horizontal.svg`  
   - Layout: icon left, “in.today” right.  
   - “in” in Ink `#020617`, dot and “today” in Service Blue or a mix of blue + accent dot.

3. **Wordmark only** (optional)  
   - File: `public/brand/in-today-wordmark.svg`  
   - Uses: when icon already appears separately.

### 2.4 Safe Area & Minimum Size

- Keep at least **½ of the dot height** as padding around the logo.  
- Icon minimum size: **24×24 px**, ideal 32×32 px for navigation.  
- Horizontal logo minimum height: **32 px** in product UI, **48 px** for marketing hero.

### 2.5 Don’ts

- Don’t change the relative position of dot and diamond.  
- Don’t stretch, skew, rotate off‑axis or add drop shadows / gradients.  
- Don’t recolor the logo randomly; use mono (single color) version if needed.  
- Don’t place the full‑color logo on strong clashing colors – prefer neutral backgrounds.

---

## 3. Color System

We use a calm, professional blue/grey base with a single bright accent that represents “today”.

### 3.1 Core Palette

| Token            | Name           | Hex       | Typical Usage                                      |
|-----------------|----------------|-----------|----------------------------------------------------|
| `primary`       | Service Blue   | `#1D4ED8` | Primary CTAs, main highlights, links               |
| `accent`        | Today Signal   | `#22C55E` | “Today” dot, success, active states, badges        |
| `bg_dark`       | Night Navy     | `#020617` | Dark mode page background, dark hero               |
| `bg_light`      | Fog Grey       | `#F4F4F5` | Light page background                              |
| `text_main`     | Ink            | `#020617` | Main text on light                                 |
| `text_muted`    | Slate          | `#4B5563` | Secondary text, descriptions                       |
| `border_light`  | Soft Slate     | `#E5E7EB` | Dividers, card borders (light mode)                |
| `border_dark`   | Dark Slate     | `#475569` | Dividers, card borders (dark mode)                 |
| `surface_light` | Card Light     | `#FFFFFF` | Cards, panels (light mode)                         |
| `surface_dark`  | Card Dark      | `#020617` | Cards, panels (dark mode)                          |
| `error`         | Signal Red     | `#DC2626` | Validation, error labels                           |
| `warning`       | Amber          | `#F59E0B` | Non‑critical alerts                                |
| `info`          | Info Blue      | `#0EA5E9` | Info badges, hints                                 |

### 3.2 Semantic UI Tokens

These are conceptual mappings for Tailwind / CSS variables:

- **Text**
  - `text-primary` → `#020617`
  - `text-secondary` → `#4B5563`
  - `text-on-dark` → `#F9FAFB`

- **Brand**
  - `bg-brand` / `text-brand` / `border-brand` → `#1D4ED8`

- **Accent**
  - `bg-accent` / `text-accent` → `#22C55E`

- **Backgrounds**
  - `bg-page` (light) → `#F4F4F5`
  - `bg-card` (light) → `#FFFFFF`
  - `.dark bg-page` → `#020617`
  - `.dark bg-card` → `#020617` with subtle border

- **Borders**
  - `border-default` (light) → `#E5E7EB`
  - `.dark border-default` → `#475569`

- **States**
  - `bg-success` → `#22C55E`  
  - `bg-error` → `#DC2626`  
  - `bg-warning` → `#F59E0B`  
  - `bg-info` → `#0EA5E9`  

Dark mode remains class‑based (`.dark` on `<html>`) with overrides in one place.

---

## 4. Typography

We favor system fonts for speed and a clean “product” feeling.

### 4.1 Primary Typeface

System UI stack:

- `system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`

Usage:

- Headlines (H1–H2): 700 / bold  
- Section titles (H3–H4): 600 / semibold  
- Body text: 400–500  
- Buttons and labels: 600, sentence case (avoid ALL CAPS)

### 4.2 Scale (Web)

Approximate base scale:

- H1: 40–48 px  
- H2: 30–36 px  
- H3: 24–28 px  
- Body: 16–18 px  
- Small text: 13–14 px  

Line‑height: 1.3–1.6 depending on context.

---

## 5. UI Style

### 5.1 Buttons

**Primary button**

- Background: `bg-brand`  
- Text: white (`text-on-dark`)  
- Hover: darker blue (`#1E40AF`)  
- Focus: 2px outline in `accent` green

**Secondary button**

- Background: transparent / card background  
- Border: `border-brand`  
- Text: `text-brand`  
- Hover: light blue tint in background

**Ghost button**

- No border, transparent background  
- Text: `text-secondary` → `text-brand` on hover

### 5.2 Cards & Layout

- Border radius: 8–16 px  
- Either 1px border or very soft shadow (never both heavy)  
- Padding: 20–32 px for sections/cards  
- Vertical spacing: multiples of **8 px** for rhythm

### 5.3 Forms

- Inputs: neutral border (`border-default`), rounded corners  
- Focus: `border-brand` + subtle outline  
- Error: `border-error` + small red message under field  
- Use clear, concise labels; placeholders are hints, not labels.

---

## 6. Tone of Voice

We speak to **restaurant operators** and **guests** in a calm, clear, confident way.

### 6.1 Principles

- Clear over clever – explain exactly what happens.  
- Respect the operator’s time – short, concrete sentences.  
- Warm but not cheesy – friendly, no forced humor.  
- Slightly European flavor – avoid very American buzzwords.

### 6.2 Examples (B2B – Restaurants)

- “Fill tonight’s empty tables with real guests.”  
- “We handle discovery and reservations; you focus on hospitality.”  
- “Transparent pricing, no long contracts.”  

### 6.3 Examples (B2C – Guests)

- “Find a great place for tonight.”  
- “See who has tables available now.”  
- “Book in seconds, pay at the restaurant.”  

---

## 7. Asset Locations & Naming

Within the repo, brand assets live under:

- `public/brand/in-today-logo-icon.svg`  
- `public/brand/in-today-logo-horizontal.svg`  
- `public/brand/in-today-wordmark.svg` (optional)  
- `public/brand/in-today-favicon.svg` / `favicon.ico` (from icon)

OpenGraph / social image:

- `public/img/og-in-today.jpg` – 1200×630 px, dark background, logo centered.

---

## 8. Implementation Notes (Developers)

- Tailwind should use the palette above via custom CSS variables and semantic classes (`bg-page`, `bg-card`, `text-primary`, etc.).  
- Dark mode stays class‑based (`.dark`) with variables overridden in one file.  
- Use the **icon only** variant for favicons and avatars; use the **horizontal logo** for navigation bars and public marketing pages.

---

## 9. Version History

- **v2.0** – New blue/green palette, refined icon concept, clarified logo variants, added tone of voice and implementation notes.  
- **v1.x** – Early Indigo/Coral palette and first logo experiments (deprecated).
