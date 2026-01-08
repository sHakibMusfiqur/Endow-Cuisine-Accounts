# 🎯 Logo Design Quick Reference

## HTML Structure
```html
<a href="{{ route('dashboard') }}" 
   class="logo {{ request()->routeIs('dashboard') ? 'active' : '' }}"
   aria-label="Go to Dashboard - Restaurant Accounting System"
   data-tooltip="🏠 Dashboard">
    <div class="logo-icon">
        <i class="fas fa-utensils"></i>
    </div>
    <div class="logo-text">
        <span class="app-name">Endow Cuisine</span>
        <span class="app-tagline">Accounting System</span>
    </div>
</a>
```

## CSS Classes
```css
.logo                    /* Main container */
.logo-icon               /* Icon wrapper */
.logo-text               /* Text container */
.app-name                /* Primary name */
.app-tagline             /* Secondary text */
.logo.active             /* Dashboard active state */
.sidebar-collapsed .logo /* Collapsed state */
```

## Key Sizes
| Element | Expanded | Collapsed |
|---------|----------|-----------|
| Sidebar | 250px | 70px |
| Icon Size | 26px | 28px |
| Icon Container | 44x44px | 48x48px |
| App Name | 18px | hidden |
| Tagline | 11px | hidden |

## Colors
```css
Gold:     #ffd700
Dark BG:  #2c3e50
Light BG: #34495e
White:    rgba(255,255,255,0.8)
```

## Transitions
```css
Duration: 0.3s
Easing:   cubic-bezier(0.4, 0, 0.2, 1)
```

## Hover Effects
- Icon: `scale(1.1) rotate(-5deg)`
- Text: Color → `#ffd700`
- Shadow: `0 4px 12px rgba(255,215,0,0.3)`

## Responsive Breakpoints
- Desktop: > 1024px
- Tablet: 769-1024px
- Mobile: 481-768px
- XSmall: ≤ 480px

## Accessibility
- Minimum touch target: 44x44px ✅
- ARIA labels: Required ✅
- Keyboard focus: Visible outline ✅
- Color contrast: WCAG AA ✅
- Reduced motion: Supported ✅

## Browser Support
✅ Chrome 90+  
✅ Firefox 88+  
✅ Safari 14+  
✅ Edge 90+  

## Toggle Sidebar (JavaScript)
```javascript
document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
```

---
📝 **Note**: See `LOGO_DESIGN_GUIDE.md` for complete documentation.
