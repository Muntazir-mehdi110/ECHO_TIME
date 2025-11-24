<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Handle reorder functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    if ($order_id > 0) {
        $stmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $order_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $added = 0;
        while ($r = mysqli_fetch_assoc($res)) {
            cart_add((int)$r['product_id'], (int)$r['quantity']);
            $added += (int)$r['quantity'];
        }
        mysqli_stmt_close($stmt);
        set_message("Added {$added} item(s) from Order #{$order_id} to your cart.", 'success');
    } else {
        set_message("Invalid order for reorder.", 'error');
    }
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ FIX 1: Fetch payment, shipping, and tracking info properly
$sql = "
SELECT o.id, o.total_amount, o.payment_method, o.payment_reference, 
       o.shipping_address, 
       COALESCE(o.tracking_number, '') AS tracking_number,
       o.status, o.created_at
FROM orders o

WHERE user_id = ?
ORDER BY created_at DESC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$orders = [];
while ($order = mysqli_fetch_assoc($res)) {

    // ✅ FIX 2: Fetch ordered items with product details
    $items_sql = "
        SELECT 
            oi.product_id, oi.quantity, oi.price,
            p.name, p.image, p.discount
        FROM order_items oi
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ";
    $stmt2 = mysqli_prepare($conn, $items_sql);
    mysqli_stmt_bind_param($stmt2, 'i', $order['id']);
    mysqli_stmt_execute($stmt2);
    $items_res = mysqli_stmt_get_result($stmt2);

    $items = [];
    $preview = [];
    $total_items = 0;
    $i = 0;

    while ($item = mysqli_fetch_assoc($items_res)) {
        $price = (float)$item['price'];
        $discount = isset($item['discount']) ? (float)$item['discount'] : 0;
        $final_price = $price - ($price * ($discount / 100));
        $total_items += (int)$item['quantity'];

        // ✅ ensure image fallback
        $item['image'] = $item['image'] ?: 'product-placeholder.jpg';
        $item['final_price'] = $final_price;

        $items[] = $item;
        if ($i < 3) { // show only first 3 in preview
            $preview[] = ['name' => $item['name'], 'image' => $item['image']];
        }
        $i++;
    }

    mysqli_stmt_close($stmt2);

    // ✅ FIX 3: Make sure payment method label is clear
    $method = strtolower($order['payment_method']);
    if ($method === 'cod' || $method === 'cash_on_delivery') {
        $method = 'Cash on Delivery';
    } elseif ($method === 'payfast') {
        $method = 'PayFast (Online Payment)';
    } elseif ($method === 'jazzcash') {
        $method = 'JazzCash';
    } else {
        $method = ucfirst($method ?: 'Not Specified');
    }

    // ✅ FIX 4: Compute status info and tracking
    $status = ucfirst($order['status'] ?: 'Pending');
    $tracking = $order['tracking_number'] ?: 'Not yet shipped';

    $orders[] = [
        'id' => $order['id'],
        'total_amount' => $order['total_amount'],
        'payment_method' => $method,
        'payment_reference' => $order['payment_reference'] ?? '',
        'shipping_address' => $order['shipping_address'] ?? 'Not provided',
        'tracking_number' => $tracking,
        'status' => $status,
        'created_at' => $order['created_at'],
        'items_preview' => $preview,
        'items_full' => $items,
        'total_items' => $total_items
    ];
}
mysqli_stmt_close($stmt);

// ✅ helper for order status badge styling
function get_status_class($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return ['class' => 'status-pending', 'icon' => '⏳'];
        case 'shipped':
            return ['class' => 'status-shipped', 'icon' => '🚚'];
        case 'delivered':
            return ['class' => 'status-delivered', 'icon' => '✅'];
        case 'cancelled':
            return ['class' => 'status-cancelled', 'icon' => '❌'];
        default:
            return ['class' => 'status-default', 'icon' => 'ℹ️'];
    }
}


include __DIR__ . '/../includes/header.php';
?>



<!-- Page Content -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
<style>
/* Modern Orders Page styles (self-contained) */
:root{
  --primary:#0d52a0;
  --accent:#ff5e5e;
  --glass: rgba(255,255,255,0.65);
  --muted:#6b7280;
  --card-shadow: 0 10px 30px rgba(13,37,63,0.08);
}

body {
  background: linear-gradient(180deg,#eef2f7 0%, #f7fbff 100%);
  font-family: 'Poppins', sans-serif;
}

/* Container */
.orders-page {
  max-width: 1200px;
  margin: 48px auto;
  padding: 0 18px 80px;
}

/* Heading */
.orders-header {
  text-align: center;
  margin-bottom: 18px;
}
.orders-header h1 {
  font-family: 'Playfair Display', serif;
  color: var(--primary);
  font-size: 2.4rem;
  margin: 0;
  letter-spacing: 0.2px;
}
.orders-header p {
  margin: 6px 0 0;
  color: var(--muted);
}

/* Grid */
.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 22px;
  margin-top: 28px;
}

/* Card */
.order-card {
  background: var(--glass);
  border-radius: 14px;
  padding: 18px;
  box-shadow: var(--card-shadow);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(13,82,160,0.06);
  transition: transform .25s ease, box-shadow .25s ease;
  position: relative;
  overflow: hidden;
}
.order-card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(13,37,63,0.12); }

/* Header row inside card */
.card-head {
  display:flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  margin-bottom: 12px;
}
.card-head .order-id { font-weight:600; color:var(--primary); }
.card-head .order-date { color:var(--muted); font-size:0.92rem; }

/* Status badge */
.status-badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-weight:600;
  color: #fff;
  display:inline-flex;
  gap:8px;
  align-items:center;
  font-size:0.92rem;
  box-shadow: 0 6px 18px rgba(13,37,63,0.06);
}

/* Status colors */
.status-pending { background: linear-gradient(135deg,#ffb300,#ff8a00); }
.status-shipped { background: linear-gradient(135deg,#1e90ff,#00bcd4); }
.status-delivered { background: linear-gradient(135deg,#43a047,#66bb6a); }
.status-cancelled { background: linear-gradient(135deg,#e53935,#ff5252); }
.status-default { background: #9e9e9e; }

/* Body */
.card-body { display:flex; align-items:flex-start; gap:16px; flex-wrap:wrap; }
.order-summary { flex:1 1 180px; min-width:160px; }
.order-summary .label { color:var(--muted); font-size:0.92rem; display:block; margin-bottom:4px; }
.order-summary .amount { font-weight:700; color:var(--primary); font-size:1.05rem; }

/* Items preview */
.items-preview { display:flex; gap:8px; align-items:center; margin-top:6px; }
.item-thumb { width:56px; height:56px; object-fit:cover; border-radius:8px; border:1px solid rgba(255,255,255,0.6); box-shadow:0 6px 18px rgba(13,37,63,0.06); cursor:pointer; transition: transform .18s; }
.item-thumb:hover { transform: scale(1.06); }
.more-count { color:var(--primary); font-weight:600; margin-left:6px; font-size:0.95rem; }

/* Actions */
.card-actions { display:flex; gap:10px; margin-top:14px; flex-wrap:wrap; }
.btn {
  padding:8px 14px;
  border-radius:999px;
  border:1px solid transparent;
  cursor:pointer;
  font-weight:600;
  font-size:0.92rem;
  background:#fff;
}
.btn-outline { background:transparent; border-color:var(--primary); color:var(--primary); }
.btn-outline:hover { background:var(--primary); color:#fff; box-shadow:0 8px 16px rgba(13,82,160,0.12); }
.btn-primary { background:linear-gradient(135deg,var(--primary),#0a7fde); color:#fff; border:none; }
.btn-primary:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(13,82,160,0.16); }

/* Empty state */
.empty-state {
  text-align:center;
  margin-top:30px;
  padding:40px;
  background:linear-gradient(180deg, rgba(255,255,255,0.7), rgba(255,255,255,0.55));
  border-radius:12px;
  box-shadow: var(--card-shadow);
}
.empty-icon { font-size:48px; color:var(--muted); margin-bottom:10px; }

/* Modal */
.modal-backdrop {
  position:fixed; inset:0; background:rgba(10,20,40,0.45); display:none; align-items:center; justify-content:center; z-index:9999;
}
.modal {
  width: min(980px, 96%);
  background:#fff;
  border-radius:12px;
  padding:18px;
  box-shadow:0 18px 48px rgba(10,20,40,0.35);
  max-height:86vh;
  overflow:auto;
}

/* Modal header */
.modal-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px; }
.modal-title { font-weight:700; color:var(--primary); }
.modal-close { background:transparent; border:none; font-size:20px; cursor:pointer; }

/* Modal content */
.modal-grid { display:grid; grid-template-columns: 1fr 320px; gap:18px; }
@media (max-width:900px) { .modal-grid { grid-template-columns: 1fr; } }

.modal-items { display:flex; flex-direction:column; gap:12px; }
.modal-item { display:flex; gap:12px; align-items:center; border-radius:10px; padding:10px; background:#fafafa; }
.modal-item img { width:72px; height:72px; object-fit:cover; border-radius:8px; }

.modal-summary { background:#fafafa; padding:12px; border-radius:10px; }

/* Animations */
@keyframes cardIn { from { transform: translateY(6px); opacity:0 } to { transform: none; opacity:1 } }
.order-card { animation: cardIn .4s ease both; }

@media (max-width:720px) {
  .orders-page { padding: 24px 14px 60px; }
  .orders-header h1 { font-size: 1.6rem; }
}
</style>

<main class="orders-page">
  <div class="orders-header">
    <h1>My Orders</h1>
    <p>Track your recent purchases — view details, reorder, or request a return.</p>
  </div>

  <?php display_message(); ?>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>No orders yet</h3>
      <p>You haven't placed any orders. Start shopping to see them here.</p>
      <p style="margin-top:12px;"><a class="btn-primary btn" href="<?= $base ?>/pages/shop.php">Go to Shop</a></p>
    </div>
  <?php else: ?>
    <div class="orders-grid">
      <?php foreach ($orders as $o): 
        $status_data = get_status_class($o['status']);
        $status_class = $status_data['class'];
        $status_icon = $status_data['icon'];
      ?>
        <article class="order-card" aria-labelledby="order-<?=esc($o['id'])?>">
          <div class="card-head">
            <div>
              <div class="order-id" id="order-<?=esc($o['id'])?>">Order #<?= esc($o['id']) ?></div>
              <div class="order-date"><?= date('M j, Y', strtotime($o['created_at'])) ?></div>
            </div>
             
            <div>
              <span class="status-badge <?=esc($status_class)?>">
                <span style="font-size:0.95rem;"><?=esc($status_icon)?></span>
                <span style="margin-left:6px; text-transform:capitalize;"><?=esc($o['status'])?></span>
              </span>
            </div>
          </div>
<?php if (!empty($o['tracking_number'])): ?>
    <p><strong>Tracking Number:</strong> <?= esc($o['tracking_number']) ?></p>
<?php else: ?>
    <p><strong>Tracking Number:</strong> <em style="color:#999;">Not yet assigned</em></p>
<?php endif; ?>

          <div class="card-body">
            <div class="order-summary">
              <span class="label">Total</span>
              <div class="amount">₹<?= formatPrice($o['total_amount']) ?></div>

              <div class="label" style="margin-top:8px;">Items</div>
              <div class="items-preview" title="<?=esc($o['total_items'])?> item(s)">
                <?php foreach ($o['items_preview'] as $pi): ?>
                  <img src="../uploads/<?= esc($pi['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($pi['name']) ?>" class="item-thumb" data-order-id="<?=esc($o['id'])?>" />
                <?php endforeach; ?>
                <?php if ($o['total_items'] > count($o['items_preview'])): ?>
                  <div class="more-count">+<?= ($o['total_items'] - count($o['items_preview'])) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div style="min-width:180px;">
              <div class="label">Payment</div>
              <div style="font-weight:700;color:#444; margin-top:6px;"><?= esc($o['payment_method'] ?? 'Not specified') ?></div>
              <div style="margin-top:10px; color:var(--muted); font-size:0.9rem;">
                <div><strong>Order Date:</strong> <?= date('M j, Y, H:i', strtotime($o['created_at'])) ?></div>
                <div><strong>Items:</strong> <?= esc($o['total_items'] ?? 1) ?></div>
              </div>
            </div>
          </div>

          <div class="card-actions">
            <!-- View details opens modal -->
            <button class="btn btn-outline" type="button" data-action="open-details" data-order-id="<?=esc($o['id'])?>">View Details</button>

            <!-- Reorder button (server-handled) -->
            <form method="post" style="display:inline;">
              <input type="hidden" name="action" value="reorder">
              <input type="hidden" name="order_id" value="<?=esc($o['id'])?>">
              <button class="btn btn-primary" type="submit" onclick="return confirm('Add items from Order #<?=esc($o['id'])?> to your cart?');">Reorder</button>
            </form>

            <?php if (strtolower($o['status']) === 'delivered'): ?>
              <a href="return.php?order_id=<?=esc($o['id'])?>" class="btn btn-outline">Request Return</a>
            <?php endif; ?>
          </div>

          <!-- Hidden detailed markup for modal (rendered server-side so modal displays quickly) -->
          <div id="order-details-<?=esc($o['id'])?>" style="display:none;">
            <div style="padding:8px 4px;">
              <h3 style="margin:0 0 8px;">Order #<?=esc($o['id'])?> — Details</h3>
              <div style="margin-bottom:8px;color:var(--muted);">Placed on <?= date('M j, Y, H:i', strtotime($o['created_at'])) ?> • Payment: <?= esc($o['payment_method'] ?? 'Not specified') ?></div>

              <div style="display:flex;flex-direction:column;gap:10px;">
                <?php
                // If get_order_items_preview returned full items in 'items', use that; otherwise fetch order_items
                $items_for_modal = $o['items_full'] ?? null;
                if (empty($items_for_modal)) {
                  $stmt2 = mysqli_prepare($conn, "SELECT oi.quantity, oi.price, oi.product_id, p.name, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?");
                  mysqli_stmt_bind_param($stmt2, 'i', $o['id']);
                  mysqli_stmt_execute($stmt2);
                  $res2 = mysqli_stmt_get_result($stmt2);
                  $items_for_modal = [];
                  while ($row2 = mysqli_fetch_assoc($res2)) $items_for_modal[] = $row2;
                  mysqli_stmt_close($stmt2);
                }
                ?>
                <?php foreach ($items_for_modal as $it): ?>
                  <div style="display:flex;gap:12px;align-items:center;padding:10px;border-radius:8px;background:#fff;">
                    <img src="../uploads/<?= esc($it['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($it['name']) ?>" style="width:72px;height:72px;object-fit:cover;border-radius:8px;">
                    <div style="flex:1;">
                      <div style="font-weight:700;"><?= esc($it['name']) ?></div>
                      <div style="color:var(--muted);font-size:0.95rem;margin-top:6px;">Qty: <?= esc($it['quantity'] ?? 1) ?> • Price: ₹<?= formatPrice($it['price'] ?? ($it['price'] ?? 0)) ?></div>
                    </div>
                    <div style="font-weight:700;color:var(--primary);">₹<?= formatPrice( ($it['price'] ?? 0) * ($it['quantity'] ?? 1) ) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                <div style="color:var(--muted);">Shipping address</div>
                 <?php
$tracking_display = !empty($o['tracking_number']) ? $o['tracking_number'] : 'No tracking yet';
?>
<div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
  <div style="color:var(--muted);">Tracking number</div>
  <div style="font-weight:700;"><?= esc($tracking_display) ?></div>
</div>

                <div style="font-weight:700;"><?= esc($o['shipping_address'] ?? 'Not provided') ?></div>
              </div>

              <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                <div style="color:var(--muted);">Order total</div>
                <div style="font-weight:800; color:var(--primary);">₹<?= formatPrice($o['total_amount']) ?></div>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<!-- Modal markup -->
<div class="modal-backdrop" id="modalBackdrop" role="dialog" aria-hidden="true">
  <div class="modal" role="document" aria-labelledby="modalTitle" aria-describedby="modalDesc">
    <div class="modal-header">
      <div class="modal-title" id="modalTitle">Order Details</div>
      <button class="modal-close" id="modalClose" aria-label="Close">&times;</button>
    </div>
    <div id="modalContent"></div>
  </div>
</div>

<script>
(function(){
  const backdrop = document.getElementById('modalBackdrop');
  const modalContent = document.getElementById('modalContent');
  const modalClose = document.getElementById('modalClose');

  // open modal when clicking "View Details" and populate content from hidden server-rendered element
  document.querySelectorAll('[data-action="open-details"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = btn.getAttribute('data-order-id');
      const hidden = document.getElementById('order-details-' + id);
      if (!hidden) return;
      modalContent.innerHTML = hidden.innerHTML;
      backdrop.style.display = 'flex';
      backdrop.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    });
  });

  function closeModal() {
    modalContent.innerHTML = '';
    backdrop.style.display = 'none';
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  modalClose.addEventListener('click', closeModal);
  backdrop.addEventListener('click', (e) => {
    if (e.target === backdrop) closeModal();
  });

  // keyboard close (ESC)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && backdrop.style.display === 'flex') closeModal();
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
