<?php
/**
 * HerMart — Admin: Categories
 *
 * CRUD on the categories table. Slug is auto-generated from the name.
 */
$page_title = 'Categories — Admin — HerMart';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin-auth.php';

admin_only();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'update') {
        $name        = trim($_POST['name'] ?? '');
        $icon        = trim($_POST['icon'] ?? 'category');
        $description = trim($_POST['description'] ?? '');
        $slug_input  = trim($_POST['slug'] ?? '');

        if ($name === '') {
            $error = 'Name is required.';
        } else {
            $slug = $slug_input !== '' ? strtolower(preg_replace('/[^a-z0-9-]+/i', '-', $slug_input)) : strtolower(preg_replace('/[^a-z0-9-]+/i', '-', $name));

            if ($action === 'create') {
                $dup = db_select_one("SELECT id FROM categories WHERE slug = ?", 's', [$slug]);
                if ($dup) {
                    $error = 'A category with that slug already exists.';
                } else {
                    db_execute(
                        "INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)",
                        'ssss', [$name, $slug, $icon, $description]
                    );
                    $success = 'Category added.';
                }
            } else {
                $id = safe_int($_POST['id'] ?? 0);
                if ($id > 0) {
                    db_execute(
                        "UPDATE categories SET name=?, slug=?, icon=?, description=? WHERE id=?",
                        'ssssi', [$name, $slug, $icon, $description, $id]
                    );
                    $success = 'Category updated.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = safe_int($_POST['id'] ?? 0);
        if ($id > 0) {
            // Block deletion when in use.
            $used = db_select_one("SELECT COUNT(*) AS c FROM products WHERE category_id = ?", 'i', [$id]);
            if ($used && (int)$used['c'] > 0) {
                $error = 'Cannot delete: this category is used by ' . (int)$used['c'] . ' product(s).';
            } else {
                db_execute("DELETE FROM categories WHERE id = ?", 'i', [$id]);
                $success = 'Category removed.';
            }
        }
    }
}

$rows = db_select("SELECT * FROM categories ORDER BY name ASC");

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/_layout_top.php';
?>

<?php if ($error): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-red-50 text-red-700 border-red-200"><?= h($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="rounded-xl px-4 py-3 text-sm font-medium border bg-emerald-50 text-emerald-700 border-emerald-200"><?= h($success) ?></div>
<?php endif; ?>

<div>
  <h2 class="text-xl font-bold">Categories</h2>
  <p class="text-xs text-on-surface-variant"><?= count($rows) ?> total</p>
</div>

<div class="bg-white rounded-3xl border border-outline/40 p-6">
  <h3 class="font-bold mb-3">Add a category</h3>
  <form method="post" class="grid sm:grid-cols-4 gap-3">
    <input type="hidden" name="action" value="create">
    <div>
      <label class="hm-label">Name *</label>
      <input type="text" name="name" required class="hm-input" placeholder="e.g. Beauty">
    </div>
    <div>
      <label class="hm-label">Slug (optional)</label>
      <input type="text" name="slug" class="hm-input" placeholder="beauty">
    </div>
    <div>
      <label class="hm-label">Icon</label>
      <input type="text" name="icon" class="hm-input" value="category" placeholder="category">
    </div>
    <div>
      <label class="hm-label">Description</label>
      <input type="text" name="description" class="hm-input" placeholder="Optional">
    </div>
    <div class="sm:col-span-4 flex justify-end">
      <button type="submit" class="hm-btn hm-btn-primary">
        <span class="material-symbols-outlined text-base">add</span>
        Add Category
      </button>
    </div>
  </form>
</div>

<div class="bg-white rounded-3xl border border-outline/40 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-xs uppercase tracking-wider text-on-surface-variant bg-surface-container/40 border-b border-outline/40">
        <tr>
          <th class="text-left py-3 px-4">Category</th>
          <th class="text-left">Slug</th>
          <th class="text-left">Description</th>
          <th></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-outline/40">
        <?php foreach ($rows as $c): ?>
          <tr>
            <td class="py-3 px-4">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary"><?= h($c['icon'] ?: 'category') ?></span>
                <span class="font-semibold"><?= h($c['name']) ?></span>
              </div>
            </td>
            <td class="text-xs"><?= h($c['slug']) ?></td>
            <td class="text-xs"><?= h($c['description'] ?? '') ?></td>
            <td class="pr-4 text-right">
              <details class="inline-block">
                <summary class="text-primary font-bold text-xs cursor-pointer hover:underline">Edit</summary>
                <form method="post" class="mt-2 p-3 bg-surface-container/30 rounded-xl grid grid-cols-2 gap-2 text-left">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <div>
                    <label class="hm-label">Name</label>
                    <input type="text" name="name" required class="hm-input" value="<?= h($c['name']) ?>">
                  </div>
                  <div>
                    <label class="hm-label">Slug</label>
                    <input type="text" name="slug" class="hm-input" value="<?= h($c['slug']) ?>">
                  </div>
                  <div>
                    <label class="hm-label">Icon</label>
                    <input type="text" name="icon" class="hm-input" value="<?= h($c['icon']) ?>">
                  </div>
                  <div>
                    <label class="hm-label">Description</label>
                    <input type="text" name="description" class="hm-input" value="<?= h($c['description'] ?? '') ?>">
                  </div>
                  <div class="col-span-2 flex justify-end">
                    <button type="submit" class="hm-btn hm-btn-primary text-xs">Save</button>
                  </div>
                </form>
              </details>
              <div class="admin-actions inline-block">
                <form method="post" onsubmit="return confirm('Delete this category?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button type="submit" class="hm-btn hm-btn-outline text-red-500 text-xs">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
