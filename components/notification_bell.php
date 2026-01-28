<!-- Notification Bell Component v2 -->
<?php
// Get user info
$user_type_notif = $_SESSION['role'];
$user_id_notif = 0;

if ($user_type_notif == 'mahasiswa') {
    $user_id_notif = isset($id_mahasiswa) ? $id_mahasiswa : (isset($mhs['id_mahasiswa']) ? $mhs['id_mahasiswa'] : 0);
} elseif ($user_type_notif == 'dosen') {
    $user_id_notif = isset($id_dosen) ? $id_dosen : (isset($dosen['id_dosen']) ? $dosen['id_dosen'] : 0);
}

// Get unread count
$unread_count_notif = 0;
if ($user_id_notif > 0 && file_exists(__DIR__ . '/../actions/notification_helper.php')) {
    include_once __DIR__ . '/../actions/notification_helper.php';
    $unread_count_notif = getUnreadCount($conn, $user_type_notif, $user_id_notif);
}

$is_dosen_dashboard = ($user_type_notif == 'dosen');
$bell_id = 'notifBell_' . uniqid(); // Unique ID
$dropdown_id = 'notifDrop_' . uniqid();
$list_id = 'notifList_' . uniqid();
?>

<!-- Notification Bell -->
<div class="relative">
    <button 
        id="<?php echo $bell_id; ?>"
        type="button"
        class="relative p-2 rounded-lg transition <?php echo $is_dosen_dashboard ? 'text-white hover:bg-white/20' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50'; ?>">
        <i class="fas fa-bell text-xl"></i>
        <?php if ($unread_count_notif > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                <?php echo $unread_count_notif > 9 ? '9+' : $unread_count_notif; ?>
            </span>
        <?php endif; ?>
    </button>
    
    <div id="<?php echo $dropdown_id; ?>" style="display:none; position:fixed; z-index:9999;" class="w-80 sm:w-96 bg-white rounded-lg shadow-2xl border max-h-96 overflow-y-auto">
        <div class="p-4 border-b flex justify-between items-center sticky top-0 bg-white">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-bell text-blue-600"></i> Notifikasi
            </h3>
            <button type="button" class="notif-mark-all text-xs text-blue-600 hover:text-blue-800 font-semibold">
                Tandai Semua Dibaca
            </button>
        </div>
        <div id="<?php echo $list_id; ?>" class="divide-y">
            <div class="p-4 text-center text-gray-500 text-sm">
                <i class="fas fa-spinner fa-spin"></i> Memuat...
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var bellBtn = document.getElementById('<?php echo $bell_id; ?>');
    var dropdown = document.getElementById('<?php echo $dropdown_id; ?>');
    var listEl = document.getElementById('<?php echo $list_id; ?>');
    var isOpen = false;
    
    if (!bellBtn || !dropdown || !listEl) {
        console.error('Notification bell elements not found');
        return;
    }
    
    console.log('Notification bell initialized');
    
    // Toggle dropdown
    bellBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log('Bell clicked');
        
        if (isOpen) {
            dropdown.style.display = 'none';
            isOpen = false;
        } else {
            // Calculate position
            var rect = bellBtn.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + 5) + 'px';
            dropdown.style.left = (rect.right - 384) + 'px'; // 384px = w-96
            
            dropdown.style.display = 'block';
            isOpen = true;
            loadNotifications();
        }
    });
    
    // Close when click outside
    document.addEventListener('click', function(e) {
        if (isOpen && !dropdown.contains(e.target) && e.target !== bellBtn) {
            dropdown.style.display = 'none';
            isOpen = false;
        }
    });
    
    // Mark all as read
    var markAllBtn = dropdown.querySelector('.notif-mark-all');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('../actions/notification_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'mark_all_read=1'
            })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    setTimeout(() => location.reload(), 300);
                }
            });
        });
    }
    
    function loadNotifications() {
        console.log('Loading notifications...');
        fetch('../actions/notification_api.php?action=get&limit=10')
            .then(r => r.json())
            .then(data => {
                console.log('Data received:', data);
                if (data.status === 'success') {
                    displayNotifications(data.notifications);
                } else {
                    listEl.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error</div>';
                }
            })
            .catch(err => {
                console.error(err);
                listEl.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Gagal memuat</div>';
            });
    }
    
    
    function displayNotifications(notifs) {
        if (!notifs || notifs.length === 0) {
            listEl.innerHTML = '<div class="p-6 text-center text-gray-400"><i class="fas fa-inbox text-3xl mb-2 block"></i>Belum ada notifikasi</div>';
            return;
        }
        
        var html = '';
        notifs.forEach(function(n) {
            var unread = !n.is_read || n.is_read == '0';
            var bg = unread ? 'bg-blue-50' : 'bg-white';
            var dot = unread ? '<div class="w-2 h-2 bg-blue-600 rounded-full mt-1.5 flex-shrink-0"></div>' : '<div class="w-2 flex-shrink-0"></div>';
            
            // Determine emoji based on title keywords
            var emoji = '📢';
            var title = n.judul || '';
            if (title.toLowerCase().includes('selesai')) emoji = '🎉';
            else if (title.toLowerCase().includes('dimulai') || title.toLowerCase().includes('proses')) emoji = '✅';
            else if (title.toLowerCase().includes('dipanggil') || title.toLowerCase().includes('panggil')) emoji = '⚠️';
            else if (title.toLowerCase().includes('booking') || title.toLowerCase().includes('antrian baru')) emoji = '📝';
            else if (title.toLowerCase().includes('dibatalkan') || title.toLowerCase().includes('batal')) emoji = '❌';
            else if (title.toLowerCase().includes('dilewati')) emoji = '⏭️';
            
            html += '<div class="p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 ' + bg + '" onclick="markRead(' + n.id_notifikasi + ')">';
            html += '<div class="flex items-start gap-3">';
            html += dot;
            html += '<div class="text-2xl flex-shrink-0">' + emoji + '</div>';
            html += '<div class="flex-1 min-w-0">';
            html += '<h4 class="font-bold text-gray-800 text-sm mb-1">' + title + '</h4>';
            html += '<p class="text-xs text-gray-600 leading-relaxed">' + (n.pesan || '') + '</p>';
            html += '</div></div></div>';
        });
        
        listEl.innerHTML = html;
    }
    
    window.markRead = function(id) {
        fetch('../actions/notification_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'mark_read=1&id_notifikasi=' + id
        })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') loadNotifications();
        });
    };
})();
</script>
