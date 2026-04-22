# Responsive Design - Quick Reference Card

## 🎯 Golden Rules

1. **Never use fixed widths** → Use `max-width` or `clamp()`
2. **Use `clamp()` for scaling** → `clamp(min, preferred, max)`
3. **Minimum touch targets** → 44px height & width
4. **Input font size** → Always 16px (iOS zoom prevention)
5. **Test on real devices** → DevTools ≠ Real phones

---

## 📱 Breakpoints

```
XS:  0px - 480px      (Small phones)
SM:  480px - 768px    (Large phones, tablets portrait)
MD:  768px - 1024px   (Tablets landscape)
LG:  1024px - 1280px  (Small desktops)
XL:  1280px - 1536px  (Large desktops)
2XL: 1536px+          (Extra large)
```

---

## 🎨 Responsive Patterns

### Typography

```css
.page-title {
    font-size: clamp(18px, 5vw, 31px);
}
.section-title {
    font-size: clamp(16px, 3vw, 18px);
}
```

### Spacing

```css
.panel {
    padding: clamp(14px, 3vw, 16px);
}
.grid {
    gap: clamp(10px, 2vw, 14px);
}
```

### Grids

```css
.grid-4 {
    grid-template-columns: repeat(
        auto-fit,
        minmax(clamp(140px, 22vw, 100%), 1fr)
    );
}
/* Auto: 1 col (mobile) → 2 col (tablet) → 4 col (desktop) */
```

### Forms

```html
<form class="grid grid-4">
    <input class="input" />
    <!-- Auto: 1 col mobile, 4 cols desktop -->
    <input class="input" />
    <input class="input" />
    <button class="primary-btn">Submit</button>
</form>
```

### Tables

```html
<div class="table-wrap">
    <table class="table">
        <!-- Scrollable on mobile -->
    </table>
</div>
```

### Buttons

```css
.primary-btn {
    min-height: var(--touch-target); /* 44px */
    padding: clamp(9px, 2vw, 10px) clamp(12px, 2vw, 14px);
}
```

---

## 🏗️ Page Structure

```html
<div class="page-stack">
    <!-- Hero section -->
    <section class="hero">
        <div class="hero-top">
            <div>Title & description</div>
            <div class="kpi">Summary card</div>
        </div>
    </section>

    <!-- Filters -->
    <section class="panel">
        <form class="grid grid-4">
            <input class="input" />
            <button class="primary-btn">Filter</button>
        </form>
    </section>

    <!-- Content -->
    <section class="panel">
        <h3 class="section-title">Title</h3>
        <div class="grid grid-3">
            <div class="kpi">Card 1</div>
            <div class="kpi">Card 2</div>
            <div class="kpi">Card 3</div>
        </div>
    </section>
</div>
```

---

## 🔍 Device Testing Sizes

| Device     | Width  | Height |
| ---------- | ------ | ------ |
| iPhone SE  | 375px  | 667px  |
| iPhone 14  | 390px  | 844px  |
| Galaxy S23 | 360px  | 800px  |
| iPad       | 768px  | 1024px |
| iPad Pro   | 1024px | 1366px |
| Laptop     | 1280px | 720px  |
| Desktop    | 1920px | 1080px |

---

## ✅ Component Checklist

### ✓ Responsive Grid

```html
<div class="grid grid-4">
    <div>Col 1</div>
    <div>Col 2</div>
    <div>Col 3</div>
    <div>Col 4</div>
</div>
```

### ✓ Responsive Form

```html
<form class="grid grid-4">
    <input class="input" />
    <!-- 16px font! -->
    <select class="select">
        <option>Option</option>
    </select>
    <input type="date" class="input" />
    <button class="primary-btn">Submit</button>
</form>
```

### ✓ Responsive Cards

```html
<div class="grid grid-3">
    <div class="kpi">
        <div class="label">LABEL</div>
        <div class="value">999</div>
    </div>
</div>
```

### ✓ Responsive Table

```html
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Col 1</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Data</td>
            </tr>
        </tbody>
    </table>
</div>
```

### ✓ Touch-Friendly Button

```html
<button class="primary-btn">
    <!-- min-height: 44px -->
    Click me
</button>
```

---

## ❌ Anti-Patterns (Don't Do This)

```css
/* ✗ Fixed width */
.container {
    width: 1200px;
}

/* ✗ Fixed font size */
h1 {
    font-size: 32px;
}

/* ✗ Fixed padding */
.panel {
    padding: 20px;
}

/* ✗ Too small touch target */
button {
    height: 28px;
}

/* ✗ Hard-coded columns */
.grid {
    grid-template-columns: repeat(4, 1fr);
}

/* ✗ Too small input font (causes iOS zoom) */
input {
    font-size: 14px;
}
```

---

## 🎯 Common Classes

| Class          | Purpose       | Mobile | Tablet | Desktop |
| -------------- | ------------- | ------ | ------ | ------- |
| `.grid-4`      | 4-col grid    | 1      | 2      | 4       |
| `.grid-3`      | 3-col grid    | 1      | 2      | 3       |
| `.grid-2`      | 2-col grid    | 1      | 1      | 2       |
| `.kpi`         | Data card     | ✓      | ✓      | ✓       |
| `.panel`       | Container     | ✓      | ✓      | ✓       |
| `.hero`        | Hero section  | Stack  | Stack  | Side    |
| `.input`       | Form input    | 100%   | 100%   | 100%    |
| `.primary-btn` | Button        | Full   | Full   | Auto    |
| `.page-stack`  | Page wrapper  | ✓      | ✓      | ✓       |
| `.table-wrap`  | Table wrapper | Scroll | Scroll | Normal  |

---

## 📐 Spacing Values

```css
/* Small screens */
clamp(10px, 1.5vw, 14px)    /* Small gap */
clamp(12px, 2vw, 18px)      /* Medium gap */
clamp(14px, 3vw, 24px)      /* Large gap */

/* Don't use fixed values! */
```

---

## 🔤 Typography Sizes

```css
.page-title {
    font-size: clamp(18px, 5vw, 31px);
}
.section-title {
    font-size: clamp(16px, 3vw, 18px);
}
.label {
    font-size: clamp(9px, 1.5vw, 10px);
}
.text {
    font-size: clamp(12px, 2vw, 14px);
}
.input {
    font-size: 16px;
} /* Always 16px! */
```

---

## 🎮 Touch Optimization

```css
/* Minimum touch target */
button {
    min-height: 44px;
}

/* Adequate spacing */
.grid {
    gap: 10px;
}

/* Smooth scrolling on iOS */
.scroll-area {
    -webkit-overflow-scrolling: touch;
}

/* Prevent zoom on input focus */
input {
    font-size: 16px;
}
```

---

## 🧪 Quick Test

```bash
# Test breakpoints in DevTools
F12 → Toggle device toolbar → Ctrl+Shift+M

# Check on real device
# - iPhone: Safari
# - Android: Chrome
```

---

## 📚 Documentation

- 📖 **RESPONSIVE_DESIGN_GUIDE.md** - Full documentation
- ✅ **RESPONSIVE_IMPLEMENTATION_CHECKLIST.md** - Developer checklist
- 📊 **RESPONSIVE_IMPROVEMENTS_SUMMARY.md** - What changed

---

## 🚀 Pro Tips

1. **Use DevTools' "Responsive Design Mode"** (Ctrl+Shift+M)
2. **Test on real devices** - Always! DevTools lies
3. **Check form inputs** - Are they 16px? (iOS zoom)
4. **No horizontal scrolling** - Ever on mobile
5. **Touch targets 44px+** - iOS standard
6. **Test slow networks** - DevTools → 4G
7. **Check dark mode** - Apply dark colors
8. **Accessibility first** - WCAG 2.1 AA minimum

---

## 🔗 Quick Links

```
Classes:        .grid, .grid-4, .kpi, .panel, .hero
Variables:      --sidebar-w, --text, --muted, --touch-target
Breakpoints:    480px, 768px, 1024px, 1280px
Functions:      clamp(), auto-fit, minmax()
```

---

## 💡 Remember

- **Mobile First** - Design for small screens first
- **Fluid Design** - Use `clamp()` instead of fixed values
- **Touch Friendly** - 44px minimum targets
- **No Horizontal Scroll** - Ever on mobile
- **Test Real Devices** - Not just DevTools
- **Always Use 16px Input Font** - Prevents iOS zoom

---

**Print this card & keep it handy! 🎯**
