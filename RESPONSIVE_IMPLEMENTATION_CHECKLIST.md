# Responsive Design Implementation Checklist

## Quick Start for New Blade Templates

Use this checklist when creating new components or pages.

### Layout Structure ✓

- [ ] Use `.page-stack` for page content wrapper
- [ ] Use `.panel` for content containers
- [ ] Use `.grid`, `.grid-2`, `.grid-3`, or `.grid-4` for layouts
- [ ] Never use fixed widths, use `clamp()` instead
- [ ] Test on mobile (< 480px), tablet (480-768px), and desktop (769px+)

### Typography ✓

- [ ] Use `clamp()` for all font sizes
- [ ] Example: `font-size: clamp(12px, 2vw, 16px);`
- [ ] Never use fixed pixel sizes for responsive text
- [ ] Minimum readable size on mobile: 14px
- [ ] Maximum size on desktop: match brand guidelines

### Spacing & Padding ✓

- [ ] Use `clamp()` for padding and margins
- [ ] Example: `padding: clamp(12px, 2vw, 18px);`
- [ ] Minimum spacing on mobile: 10px
- [ ] Maximum spacing on desktop: 24px
- [ ] Consistent gaps in flex containers

### Buttons & Interactions ✓

- [ ] Minimum touch target: 44px height/width
- [ ] Example: `min-height: var(--touch-target);`
- [ ] Adequate spacing between buttons: 8-10px
- [ ] Hover states should be smooth transitions
- [ ] Active states for visual feedback

### Forms ✓

- [ ] All inputs: 100% width in mobile view
- [ ] Font size: 16px to prevent iOS zoom
- [ ] Use grid classes for multi-column layouts
- [ ] Grid automatically adapts: no media queries needed
- [ ] Proper label placement (top on mobile, inline on desktop)
- [ ] Visible focus states for accessibility

### Tables ✓

- [ ] Use `.table-wrap` for container
- [ ] Add `-webkit-overflow-scrolling: touch;` for smooth mobile scrolling
- [ ] Reduce padding on small screens
- [ ] Use `word-break: break-word;` for long content
- [ ] Sticky headers on scroll

### Images & Media ✓

- [ ] Always use `max-width: 100%;`
- [ ] Use responsive images with `srcset` when possible
- [ ] Never use fixed aspect ratios without `aspect-ratio: auto;`
- [ ] Test image loading on slow networks (DevTools throttling)

### Navigation ✓

- [ ] Hamburger menu appears on mobile (< 768px)
- [ ] Menu items are `.nav-item` with proper spacing
- [ ] Profile dropdown closes on outside click
- [ ] Escape key closes menus
- [ ] Touch-friendly menu items (44px minimum)

### Cards & Components ✓

- [ ] Use `.kpi`, `.card`, or `.panel` classes
- [ ] All cards are responsive by default
- [ ] No fixed widths on cards
- [ ] Proper border radius: 14-22px
- [ ] Adequate internal padding

### Mobile-Specific ✓

- [ ] Sidebar is off-canvas (hidden left, slides in on toggle)
- [ ] Hero section stacks vertically on mobile
- [ ] All grids become single-column on phones
- [ ] Full-width buttons for primary actions
- [ ] Reduced visual complexity on small screens

### Testing Checklist ✓

- [ ] Test on iPhone SE (375px)
- [ ] Test on iPhone 14 (390px)
- [ ] Test on Samsung Galaxy (412px)
- [ ] Test on iPad (768px)
- [ ] Test on iPad landscape (1024px)
- [ ] Test on desktop (1280px+)
- [ ] Test on large monitor (1920px+)
- [ ] Test touch interactions on real devices
- [ ] Check for horizontal scrolling (should not exist on mobile)
- [ ] Verify text readability at all sizes
- [ ] Test form submission on mobile
- [ ] Test navigation on mobile (hamburger menu)
- [ ] Test dark mode appearance
- [ ] Test with accessibility tools
- [ ] Check performance (DevTools Performance tab)

### Performance Checklist ✓

- [ ] No layout shifts on page load
- [ ] Images are optimized (compressed)
- [ ] CSS is minified in production
- [ ] JavaScript debounced for resize/scroll events
- [ ] No console errors on any device size
- [ ] Load time < 3 seconds on 4G
- [ ] No unnecessary reflows/repaints

---

## Code Examples

### ✓ Good Responsive Form

```html
<form class="grid grid-4">
    <select name="type" class="select">
        <option value="">All types</option>
    </select>
    <select name="user_id" class="select">
        <option value="">All members</option>
    </select>
    <input type="date" name="from" class="input" />
    <input type="date" name="to" class="input" />
    <button type="submit" class="primary-btn">Filter</button>
</form>
```

**Mobile:** 1 column, full-width
**Tablet:** 2 columns
**Desktop:** 4 columns

---

### ✓ Good Responsive Cards

```html
<div class="grid grid-3">
    <div class="kpi">
        <div class="label">Users</div>
        <div class="value">1,234</div>
    </div>
    <div class="kpi">
        <div class="label">Revenue</div>
        <div class="value">$45,678</div>
    </div>
    <div class="kpi">
        <div class="label">Growth</div>
        <div class="value">23%</div>
    </div>
</div>
```

**Mobile:** 1 column
**Tablet:** 2 columns
**Desktop:** 3 columns

---

### ✓ Good Responsive Typography

```html
<h1 class="page-title">{{ $title }}</h1>
<!-- font-size: clamp(18px, 5vw, 31px) -->

<h3 class="section-title">{{ $subtitle }}</h3>
<!-- font-size: clamp(16px, 3vw, 18px) -->

<p class="page-sub">{{ $description }}</p>
<!-- font-size: clamp(11px, 2vw, 13px) -->
```

---

### ✗ Bad Responsive Code (Don't Do This)

```html
<!-- ✗ Fixed widths - breaks on mobile -->
<form style="width: 800px;">
    <!-- ✗ Fixed font size - too small on mobile -->
    <h1 style="font-size: 32px;">
        <!-- ✗ No grid system - manual layout -->
        <div style="display: flex; width: 25%;">
            <!-- ✗ Too small touch target -->
            <button style="padding: 4px 8px;">
                <!-- ✗ Fixed columns - doesn't respond -->
                <div style="grid-template-columns: repeat(4, 1fr);"></div>
            </button>
        </div>
    </h1>
</form>
```

---

## Inline Styling (When Necessary)

When you must use inline styles, follow these patterns:

### ✓ Good Inline Responsive Styles

```html
<!-- Use clamp for spacing -->
<div
    style="padding: clamp(12px, 2vw, 18px); margin-bottom: clamp(14px, 2vw, 18px);"
>
    <!-- Use min-width instead of fixed width -->
    <div style="min-width: min(100%, 300px);">
        <!-- Flex wrapping for responsive layouts -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <!-- Use grid-column: 1 / -1 for full width -->
            <input type="text" style="grid-column: 1 / -1;" />
        </div>
    </div>
</div>
```

### ✗ Bad Inline Responsive Styles

```html
<!-- ✗ Fixed width - breaks on mobile -->
<div style="width: 400px;">
    <!-- ✗ Fixed padding - no responsiveness -->
    <div style="padding: 20px;">
        <!-- ✗ No flex-wrap - overflows -->
        <div style="display: flex;">
            <!-- ✗ Fixed grid columns - doesn't respond -->
            <div style="grid-template-columns: repeat(4, 1fr);"></div>
        </div>
    </div>
</div>
```

---

## Blade Template Patterns

### Page Layout Template

```blade
@extends('layouts.app')

@section('title', 'Page Title | SIPR')
@section('pageTitle', 'Page Title')
@section('pageSubtitle', 'Brief description of the page')

@section('content')
    <div class="page-stack">
        <!-- Hero/intro section -->
        <section class="hero">
            <h2>Main Title</h2>
            <p>Description</p>
        </section>

        <!-- Filters/controls -->
        <section class="panel">
            <form method="GET" class="grid grid-4">
                <!-- Form fields -->
            </form>
        </section>

        <!-- Main content -->
        <section class="panel">
            <h3 class="section-title">Content Title</h3>
            <div class="grid grid-3">
                <!-- Cards -->
            </div>
        </section>
    </div>
@endsection
```

### Card Grid Template

```blade
<div class="grid grid-4">
    @foreach ($items as $item)
        <div class="kpi">
            <div class="label">Label</div>
            <div class="value">{{ $item->value }}</div>
            <div class="note">Additional info</div>
        </div>
    @endforeach
</div>
```

### Table Template

```blade
<section class="panel">
    <h3 class="section-title">Table Title</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Column 1</th>
                    <th>Column 2</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->field1 }}</td>
                        <td>{{ $item->field2 }}</td>
                        <td><a href="#">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="empty">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
```

---

## Common Mistakes to Avoid

1. **Using fixed widths**

    ```css
    /* ✗ Don't */
    .container {
        width: 1200px;
    }

    /* ✓ Do */
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }
    ```

2. **Forgetting to test on real devices**
    - DevTools ≠ Real phones
    - Always test touch interactions on actual devices

3. **Using too many breakpoints**

    ```css
    /* ✗ Don't create many media queries */
    @media (max-width: 600px) {
    }
    @media (max-width: 700px) {
    }
    @media (max-width: 800px) {
    }

    /* ✓ Do use fluid values with clamp() */
    font-size: clamp(14px, 2vw, 18px);
    ```

4. **Not testing form input sizes**
    - Input fonts must be 16px to prevent iOS zoom
    - Use `font-size: 16px;` on all inputs

5. **Ignoring touch target sizes**

    ```css
    /* ✗ Too small for touch */
    button {
        height: 28px;
    }

    /* ✓ Touch-friendly */
    button {
        min-height: 44px;
    }
    ```

6. **Not handling landscape orientation**
    ```css
    @media (max-height: 500px) {
        /* Reduce vertical space in landscape */
    }
    ```

---

## Quick Reference: CSS Classes

| Class          | Purpose        | Mobile     | Tablet     | Desktop      |
| -------------- | -------------- | ---------- | ---------- | ------------ |
| `.grid-4`      | 4-column grid  | 1 col      | 2 col      | 4 col        |
| `.grid-3`      | 3-column grid  | 1 col      | 2 col      | 3 col        |
| `.grid-2`      | 2-column grid  | 1 col      | 1 col      | 2 col        |
| `.kpi`         | Data card      | Responsive | Responsive | Responsive   |
| `.panel`       | Container      | Responsive | Responsive | Responsive   |
| `.hero`        | Hero section   | Stacked    | Stacked    | Side-by-side |
| `.primary-btn` | Primary action | Full width | Full width | Auto width   |
| `.table`       | Data table     | Scrollable | Scrollable | Normal       |
| `.page-stack`  | Page wrapper   | Responsive | Responsive | Responsive   |
| `.top-tools`   | Tool bar       | Stacked    | Stacked    | Horizontal   |

---

## Resources

- 📖 [Responsive Design Guide](./RESPONSIVE_DESIGN_GUIDE.md)
- 📱 [Device Breakpoints](#device-breakpoints)
- 🎨 [CSS Architecture](#css-architecture)
- 🧪 [Testing Checklist](#testing-checklist)

---

## Questions?

Refer to the [Responsive Design Guide](./RESPONSIVE_DESIGN_GUIDE.md) for detailed documentation and examples.
