# Mobile Improvements Brainstorm

## What We're Building

A comprehensive mobile polish pass for the CV chat portfolio site, addressing three reported bugs and three additional UX improvements.

## Bug Fixes

### 1. Header behind iOS Dynamic Island
The mobile header (`px-4 py-2.5`) has no safe area padding, so it sits behind the Dynamic Island/notch on modern iPhones. Fix by adding `env(safe-area-inset-top)` padding to the header.

### 2. Splash screen scroll overflow
The outer container uses `h-screen` (100vh), which doesn't account for iOS Safari's browser chrome. This causes unwanted vertical scroll on the splash screen. Fix by switching to `dvh` (dynamic viewport height), which updates as the toolbar shows/hides.

### 3. Input zoom on focus
The chat input uses `text-[0.8125rem]` (13px). iOS Safari auto-zooms any input below 16px. Fix by setting the input to 16px on mobile only. Message text stays at 13px to preserve the compact terminal aesthetic.

## Additional Improvements

### 4. Safe area insets on input area
Add `env(safe-area-inset-bottom)` padding to the chat input container so it isn't obscured by the iOS home indicator or Android gesture bars.

### 5. Touch target sizes
Ensure splash screen suggestion buttons and the send button meet the 44x44px minimum recommended touch target size for comfortable mobile tapping.

### 6. Scroll-to-bottom on keyboard open
When the mobile keyboard opens, auto-scroll the chat to the latest message so the user doesn't lose their place in the conversation.

## Key Decisions

- **Viewport unit:** Use `dvh` for the main container height (smooth resize as Safari toolbar shows/hides)
- **Input font size:** 16px on mobile only; messages stay at 13px (slight mismatch accepted to preserve terminal density)
- **Safe area insets:** Apply to both header (top) and input (bottom)
- **Scope:** All six items included in this pass

## Open Questions

None — all decisions resolved.
