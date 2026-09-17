# Global Responsive Table Layout Fix

The problem is that the global layout in `resources/views/layouts/app.php` uses `lg:flex` (1024px threshold) for the main content and the side guide. On screens like 1366x768, the sidebar (256px), padding, and guide (320px) leave the main table area with only ~718px, forcing intense horizontal scrolling on tables that need >1000px width.

## Proposed Changes

### 1. Global Layout (`resources/views/layouts/app.php`)
- Change the `main` layout grid/flex classes from `lg:flex lg:gap-6` to `2xl:flex 2xl:gap-6`.
- Change the guide wrapper from `lg:w-80 lg:mt-0` to `2xl:w-80 2xl:mt-0`.
- This ensures that on standard laptops (1366x768) and tablets, the main content takes full width, and the guide drops below the content (or can be toggled/collapsed). Only on ultra-wide screens (>=1536px) will the guide appear on the right, providing the table ample room.

### 2. Search & Filter Toolbars
- Ensure all table toolbars (`<form>`) use `flex-wrap` and flexible inputs (`min-w-[150px]` or `flex-1`) instead of rigid wide minimums.
- This prevents the toolbar itself from causing horizontal overflow on mobile screens.

### 3. Table Containers
- Audit all `index.php` views (Master Data, Transactions, Reports).
- Ensure they all follow the correct nested structure:
  ```html
  <div class="... overflow-hidden ...">
      <div class="overflow-x-auto">
          <table class="w-full min-w-[...px]">
  ```
- Remove any `overflow-x-auto` applied directly to the body or main `<main>` tag (already fixed in previous BUG-040, but will verify).
- Remove any `min-w-[...]` applied to the `page content` wrappers that would force the whole page to widen.

## User Review Required
Does this approach align with your expectations? Changing `lg:flex` to `2xl:flex` in `layouts/app.php` will move the guide to the bottom for all screens below 1536px width, immediately solving the cramped table issue for 1366x768 screens without touching the business logic or breaking the dashboard.
