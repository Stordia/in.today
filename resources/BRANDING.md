# in.today – Brand Guidelines (v1.0)

_Last updated: 2025-11-26_

## 1. Brand Essence

**in.today** is a global platform for discovering where to go out _today_:
restaurants, cafés, bars, bistros & clubs.

- **Core idea:** “Where are we going _today_?”
- **Tone:** friendly, confident, modern, service-oriented
- **Keywords:** now, nearby, together, going-out, curated

---

## 2. Logo System

### 2.1. Brand Mark

The icon consists of:

- A **rounded diamond** (tilted square) suggesting:
  - a table / meeting point
  - a map tile / location area
- A **small coral dot** sitting on top:
  - “today”, current moment
  - a person’s head → social / people / going out

This icon is our primary symbol and should work:

- as app icon
- as favicon
- as social media avatar

### 2.2. Logo Lockups

We use two main lockups:

1. **Icon only**  
   For favicons, app icons, avatars, buttons.

2. **Full logotype**  
   `⧫ in.today`  
   - “in” in neutral/black
   - the dot between “in” and “today” in **coral**
   - “today” in **indigo**

Keep enough clear space around the logo (at least the height of the coral dot on all sides).

### 2.3. Minimum Sizes

- Print: **15 mm** width (full logo), **6 mm** (icon only)
- Screen: **80 px** width (full logo), **32 px** (icon only)

Never use the logo smaller than this.

### 2.4. Incorrect Usage

Please **do not**:

- change the colors of the brand mark
- stretch, rotate or skew the logo
- add effects (glow, drop shadow, bevel, outlines)
- place the logo on noisy / low-contrast backgrounds
- change the relative size or position of the coral dot

---

## 3. Color System

### 3.1. Core Palette

| Role                | Name            | Hex       | Usage                                                 |
|---------------------|-----------------|-----------|-------------------------------------------------------|
| **Primary**         | Brand Indigo    | `#4F46E5` | CTAs, links, highlights, logo “today” text            |
| **Accent**          | Today Coral     | `#FB6A71` | The “dot”, special badges, notifications              |
| **Dark Background** | Night Navy      | `#020617` | Dark mode background, hero sections                   |
| **Light Background**| Soft Sand       | `#F5F5F0` | Light mode page background                            |
| **Text Primary**    | Ink             | `#020617` | Main body text on light background                    |
| **Text Secondary**  | Slate           | `#4B5563` | Secondary text, captions                              |
| **On Dark Text**    | Snow            | `#F9FAFB` | Main text on dark backgrounds                         |

### 3.2. Supporting Neutrals

| Name         | Hex       | Usage                                   |
|--------------|-----------|-----------------------------------------|
| Border       | `#E5E7EB` | Card borders, dividers (light)          |
| Border Dark  | `#1F2937` | Borders on dark backgrounds             |
| Card Light   | `#FFFFFF` | Cards on light background               |
| Card Dark    | `#020617` | Cards on dark background / subtle layer |

### 3.3. Tailwind Mapping (conceptual)

We represent these colors in Tailwind via semantic tokens:

- `bg-page` → page background (light / dark)
- `bg-card` → card background
- `text-primary` → main text
- `text-secondary` → secondary text
- `text-brand` → brand indigo
- `bg-brand` / `border-brand` → primary CTAs
- `bg-accent` / `text-accent` → coral accents
- `border-default` → neutral border color

Light and dark themes are handled via CSS variables and a `.dark` class on `<html>`.

---

## 4. Typography

### 4.1. Logo Typeface

- Recommended: **Nunito** (Google Fonts) or similar rounded sans serif.
- Use heavier weights (600–800) for the wordmark.

This matches the friendly, rounded feeling of the brand mark.

### 4.2. UI Typeface

- Recommended: **Inter** or **Manrope** as the primary UI font.
- Fallback stack (example):

```css
font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont,
  "Segoe UI", sans-serif;
```

### 4.3. Hierarchy (web)

- H1 (hero titles): 40–56px, bold
- H2 (section titles): 28–32px, semi-bold
- H3 (cards): 20–22px, semi-bold
- Body: 16–18px, regular
- Small text / meta: 13–14px

---

## 5. Tone of Voice

- **Friendly & helpful** – like a local who knows the city well.
- **Clear & direct** – short sentences, no jargon.
- **Inclusive** – talk to couples, groups, solo visitors.
- **Action-oriented** – always point to the next step (“Book now”, “Discover”, “See all tonight”).

Examples:

- “Find the perfect place for tonight.”
- “Dinner, cocktails or dancing? Discover what’s in today.”
- “Trusted by restaurants and food lovers across Europe.”

---

## 6. Usage Across Touchpoints

- **Website:** use Brand Indigo for key CTAs, Coral for the “dot” moments and important highlights.
- **App Icon:** icon only, on Night Navy background.
- **Emails:** use Light background with Brand Indigo headings and Coral buttons/links.
- **Social Media:** avatar = icon only; cover images = photography + Indigo overlay + logotype.

---

## 7. Files & Assets (to be added)

- `/public/brand/in-today-logo-horizontal.svg`
- `/public/brand/in-today-logo-icon.svg`
- `/public/brand/in-today-logo-horizontal-light.png`
- `/public/brand/in-today-logo-horizontal-dark.png`
- Favicons & app icons (`/public/favicon/*`)

---

_This is v1.0 of the brand guidelines and will evolve as in.today grows._
