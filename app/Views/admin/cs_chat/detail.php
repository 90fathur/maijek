<?= $this->extend('admin/layout/main') ?>

<?= $this->section('content') ?>
<style>
    /* Chat Container */
    .direct-chat-messages {
        padding: 20px;
        height: 500px;
        overflow-y: auto;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .direct-chat-msg {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    .direct-chat-msg.right {
        align-items: flex-end;
    }

    .direct-chat-infos {
        font-size: 11px;
        margin-bottom: 6px;
        color: #64748b;
        font-weight: 500;
    }

    .direct-chat-text {
        padding: 12px 18px;
        border-radius: 20px;
        display: inline-block;
        max-width: 75%;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        font-size: 14px;
        line-height: 1.5;
    }

    .bg-primary {
        background-color: #3b82f6 !important;
        /* Soft Blue */
        color: white;
        border-bottom-right-radius: 4px;
    }

    .bg-light {
        background-color: #ffffff !important;
        color: #334155;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 4px;
    }

    .bg-system {
        background-color: #fef3c7 !important;
        color: #d97706;
        border: 1px solid #fde68a;
        text-align: center;
        margin: 10px auto;
        font-style: italic;
        font-size: 13px;
        border-radius: 20px !important;
        padding: 8px 16px;
    }

    .system-msg {
        align-items: center;
    }

    /* Input Form Styles */
    .chat-input-wrapper {
        display: flex;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        padding: 5px 5px 5px 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        align-items: center;
    }

    .chat-input-wrapper input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 14px;
        color: #334155;
    }

    .chat-input-wrapper button {
        background-color: #3b82f6;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .chat-input-wrapper button:hover {
        background-color: #2563eb;
    }

    /* Card/Header Styles */
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .btn-danger {
        background-color: #ef4444;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
    }

    .btn-danger:hover {
        background-color: #dc2626;
    }

    .btn-secondary {
        background-color: #64748b;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        text-decoration: none;
        margin-left: 8px;
    }

    .btn-secondary:hover {
        background-color: #475569;
    }
</style>

<div class="row">
    <div class="col-md-8 mx-auto">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <div class="card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comments text-primary"></i> Chat CS: <?= esc($room_id) ?></h3>
                <div style="display: flex; align-items: center;">
                    <form action="<?= base_url('admin/cs-chat/resolve') ?>" method="post" style="margin: 0;" onsubmit="return confirm('Tandai sesi chat ini selesai?');">
                        <input type="hidden" name="room_id" value="<?= esc($room_id) ?>">
                        <button type="submit" class="btn-danger">
                            <i class="fas fa-check-circle"></i> Tandai Selesai
                        </button>
                    </form>
                    <a href="<?= base_url('admin/cs-chat') ?>" class="btn-secondary" title="Kembali">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="direct-chat-messages">
                    <?php
                    $isResolved = false;
                    foreach ($chats as $chat):
                        if ($chat['sender_type'] == 'system' && $chat['message'] == 'CHAT_RESOLVED') {
                            $isResolved = true;
                        }
                    ?>
                        <?php if ($chat['sender_type'] == 'admin'): ?>
                            <div class="direct-chat-msg right">
                                <div class="direct-chat-infos">
                                    <span class="direct-chat-name">Admin CS</span> -
                                    <span class="direct-chat-timestamp"><?= date('d M h:i a', strtotime($chat['created_at'])) ?></span>
                                </div>
                                <div class="direct-chat-text bg-primary">
                                    <?= esc($chat['message']) ?>
                                </div>
                            </div>
                        <?php elseif ($chat['sender_type'] == 'system'): ?>
                            <div class="direct-chat-msg system-msg">
                                <div class="direct-chat-infos">
                                    <span class="direct-chat-timestamp"><?= date('d M h:i a', strtotime($chat['created_at'])) ?></span>
                                </div>
                                <div class="direct-chat-text bg-system">
                                    <?php
                                    if ($chat['message'] == 'CHAT_RESOLVED') echo "Sesi chat telah diselesaikan.";
                                    else echo esc($chat['message']);
                                    ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="direct-chat-msg">
                                <div class="direct-chat-infos">
                                    <span class="direct-chat-name"><?= strtoupper($chat['sender_type']) ?></span> -
                                    <span class="direct-chat-timestamp"><?= date('d M h:i a', strtotime($chat['created_at'])) ?></span>
                                </div>
                                <div class="direct-chat-text bg-light">
                                    <?= esc($chat['message']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card-footer">
                <?php if ($isResolved): ?>
                    <div style="text-align: center; color: #94a3b8; padding: 15px; font-style: italic; background: #f8fafc; border-radius: 30px;">
                        Sesi chat ini sudah ditutup.
                    </div>
                <?php else: ?>
                    <form action="<?= base_url('admin/cs-chat/send') ?>" method="post">
                        <input type="hidden" name="room_id" value="<?= esc($room_id) ?>">
                        <div class="chat-input-wrapper">
                            <input type="text" name="message" id="chatInput" placeholder="Tulis balasan Anda di sini..." required autofocus autocomplete="off">
                            <button type="submit"><i class="fas fa-paper-plane"></i> Kirim</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    const messagesDiv = document.querySelector('.direct-chat-messages');

    // Auto-scroll to bottom initially
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    // Polling function to refresh chat messages
    setInterval(function() {
        // Fetch current URL again via AJAX
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                // Parse the new HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newChatHtml = doc.querySelector('.direct-chat-messages').innerHTML;

                // Check if user is near bottom before updating HTML
                const isNearBottom = messagesDiv.scrollTop >= (messagesDiv.scrollHeight - messagesDiv.clientHeight - 100);

                // Update HTML
                messagesDiv.innerHTML = newChatHtml;

                // Keep scroll at bottom if they were already at the bottom
                if (isNearBottom) {
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            })
            .catch(error => console.error('Error polling chats:', error));
    }, 5000); // Polling every 5 seconds
</script>
<?= $this->endSection() ?>