---
title: "fix: Mobile UX improvements"
type: fix
status: completed
date: 2026-04-07
---

# fix: Mobile UX improvements

## Overview

Comprehensive mobile polish pass for the CV chat portfolio site, addressing three reported bugs (Dynamic Island overlap, splash scroll overflow, input zoom) and three additional UX improvements (bottom safe area, touch targets, keyboard scroll).

## Acceptance Criteria

- [x] Header content is not obscured by the iOS Dynamic Island or notch
- [x] Splash screen fits the viewport exactly with no vertical scroll on iOS Safari
- [x] Tapping the chat input does not trigger iOS auto-zoom
- [x] Chat input is not obscured by the iOS home indicator or Android gesture bar
- [x] Suggestion buttons and send button meet 44px minimum touch target height
- [x] Chat auto-scrolls to the latest message when the mobile keyboard opens

## MVP

### 1. Safe area insets — header (top)

**File:** `resources/js/pages/Welcome.vue:29-31`

Add `env(safe-area-inset-top)` padding to the header and ensure `viewport-fit=cover` is set in the meta tag.

```html
<!-- resources/views/app.blade.php -->
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
```

```html
<!-- Welcome.vue header — add pt-[env(safe-area-inset-top)] on mobile -->
<header
  v-if="showChat"
  class="... pt-[env(safe-area-inset-top)]"
>
```

Also add safe-area top padding to the outer container so the splash screen isn't obscured either:

```html
<!-- Welcome.vue outer container -->
<div class="... pt-[env(safe-area-inset-top)]">
```

### 2. Viewport height — splash scroll fix

**File:** `resources/js/pages/Welcome.vue:24`

Replace `h-screen` (100vh) with `h-dvh` (100dvh). Tailwind v4 supports `h-dvh` natively.

```html
<!-- Before -->
<div class="flex h-screen items-center justify-center ...">

<!-- After -->
<div class="flex h-dvh items-center justify-center ...">
```

### 3. Input font size — prevent iOS auto-zoom

**File:** `resources/js/components/ChatInput.vue:187`

Bump the textarea font-size to 16px on touch/mobile devices using the existing `touch:` custom variant. Keep 13px on desktop.

```html
<!-- Before -->
<textarea class="... text-[0.8125rem] ..." />

<!-- After -->
<textarea class="... text-[0.8125rem] touch:text-base ..." />
```

`text-base` = 16px, which is the iOS threshold for disabling auto-zoom.

### 4. Safe area insets — input area (bottom)

**File:** `resources/js/components/ChatInput.vue:154`

Add `pb-[env(safe-area-inset-bottom)]` to the input container so it sits above the home indicator.

```html
<!-- Before -->
<div class="relative border-t border-od-border bg-od-bg-light px-4 py-3">

<!-- After -->
<div class="relative border-t border-od-border bg-od-bg-light px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
```

Using `max()` ensures the minimum `py-3` (0.75rem) padding is preserved on devices without a home indicator, while adding extra space on devices that have one.

### 5. Touch target sizes

**File:** `resources/js/components/SplashIntro.vue:115-122`

Increase suggestion button padding to meet 44px height. Current `py-1.5` (6px top+bottom) + `text-xs` line-height (~16px) = ~28px. Needs ~44px.

```html
<!-- Before -->
<button class="rounded border border-od-border px-3 py-1.5 text-xs ...">

<!-- After -->
<button class="rounded border border-od-border px-4 py-3 text-xs ...">
```

**File:** `resources/js/components/ChatInput.vue:190-196`

The send button (`size-4` = 16px icon, no explicit padding) needs a larger tap target. Add padding to meet 44px.

```html
<!-- Before -->
<button class="hidden text-od-blue disabled:opacity-30 touch:block" ...>
  <SendHorizontal class="size-4" />
</button>

<!-- After -->
<button class="hidden p-2 text-od-blue disabled:opacity-30 touch:block" ...>
  <SendHorizontal class="size-5" />
</button>
```

### 6. Scroll-to-bottom on keyboard open

**File:** `resources/js/components/ChatMessages.vue`

Use the `visualViewport` resize event to detect keyboard opening and scroll to bottom.

```ts
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue';

// ... existing code ...

function onViewportResize() {
  scrollToBottom();
}

onMounted(() => {
  window.visualViewport?.addEventListener('resize', onViewportResize);
});

onUnmounted(() => {
  window.visualViewport?.removeEventListener('resize', onViewportResize);
});
```

The `visualViewport` API fires resize events when the software keyboard opens/closes, which is exactly what we need. It's well-supported across modern mobile browsers.

## Files to Modify

| File | Change |
|------|--------|
| `resources/views/app.blade.php:5` | Add `viewport-fit=cover` to meta viewport |
| `resources/js/pages/Welcome.vue:24,29` | `h-dvh`, safe area top padding |
| `resources/js/components/ChatInput.vue:154,187,190` | Bottom safe area, `touch:text-base`, send button sizing |
| `resources/js/components/SplashIntro.vue:115` | Larger button padding for touch targets |
| `resources/js/components/ChatMessages.vue` | `visualViewport` resize listener for keyboard scroll |

## Testing

- [ ] Test on iPhone (Safari) — Dynamic Island not obscuring header
- [ ] Test on iPhone (Safari) — no splash screen scroll
- [ ] Test on iPhone (Safari) — input focus does not trigger zoom
- [ ] Test on iPhone (Safari) — input visible above home indicator
- [ ] Test on Android (Chrome) — gesture bar not covering input
- [ ] Test suggestion buttons are comfortable to tap on mobile
- [ ] Test chat scrolls to bottom when keyboard opens
- [ ] Verify desktop layout is unchanged (no visual regression)

## References

- [Brainstorm](../brainstorms/2026-04-07-mobile-improvements-brainstorm.md)
- [MDN: viewport-fit](https://developer.mozilla.org/en-US/docs/Web/CSS/viewport-fit)
- [MDN: env() safe-area-inset](https://developer.mozilla.org/en-US/docs/Web/CSS/env)
- [MDN: visualViewport API](https://developer.mozilla.org/en-US/docs/Web/API/VisualViewport)
