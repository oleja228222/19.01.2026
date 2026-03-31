document.addEventListener('DOMContentLoaded', () => {
    // === Автоскрытие toast ===
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // === Меню пользователя ===
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => {
            userDropdown.classList.remove('show');
        });
    }

    // === Бургер меню ===
    const burgerBtn = document.getElementById('burgerBtn');
    const nav = document.querySelector('.nav');
    if (burgerBtn && nav) {
        burgerBtn.addEventListener('click', () => {
            nav.classList.toggle('open');
        });
    }

    // === Превью аватара ===
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // === Превью обложки ===
    const coverInput = document.getElementById('coverInput');
    const coverLabel = document.getElementById('coverLabel');
    if (coverInput && coverLabel) {
        coverInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                coverLabel.textContent = '✓ ' + this.files[0].name;
                coverLabel.style.borderColor = '#48bb78';
                coverLabel.style.color = '#276749';
            }
        });
    }

    // === Лайк ===
    const likeBtn = document.getElementById('likeBtn');
    if (likeBtn && !likeBtn.disabled) {
        likeBtn.addEventListener('click', async () => {
            const articleId = likeBtn.dataset.articleId;
            try {
                const res = await fetch(SITE_URL + '/api/like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'article_id=' + articleId
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('likeCount').textContent = data.count;
                    const icon = likeBtn.querySelector('.like-icon');
                    if (data.liked) {
                        likeBtn.classList.add('liked');
                        icon.textContent = '❤️';
                    } else {
                        likeBtn.classList.remove('liked');
                        icon.textContent = '🤍';
                    }
                }
            } catch (err) {
                console.error('Like error:', err);
            }
        });
    }

    // === Комментарий ===
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const articleId = commentForm.dataset.articleId;
            const textarea = document.getElementById('commentText');
            const content = textarea.value.trim();

            if (!content) return;

            try {
                const res = await fetch(SITE_URL + '/api/comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `article_id=${articleId}&content=${encodeURIComponent(content)}`
                });
                const data = await res.json();
                if (data.success) {
                    const c = data.comment;
                    const noComments = document.getElementById('noComments');
                    if (noComments) noComments.remove();

                    const commentHtml = `
                        <div class="comment" id="comment-${c.id}">
                            <div class="comment-header">
                                <a href="${SITE_URL}/profile.php?id=${c.user_id}" class="comment-author">
                                    <img src="${c.avatar}" alt="" class="avatar-xs">
                                    <strong>${escapeHtml(c.username)}</strong>
                                </a>
                                <span class="comment-date">${c.date}</span>
                            </div>
                            <p class="comment-text">${escapeHtml(c.content)}</p>
                            <button class="comment-delete" onclick="deleteComment(${c.id})">Удалить</button>
                        </div>
                    `;
                    document.getElementById('commentsList').insertAdjacentHTML('afterbegin', commentHtml);
                    textarea.value = '';

                    // Обновить счётчик
                    const title = document.querySelector('.comments-title');
                    if (title) {
                        const count = document.querySelectorAll('.comment').length;
                        title.textContent = `💬 Комментарии (${count})`;
                    }

                    showToast('Комментарий добавлен', 'success');
                } else {
                    showToast(data.error || 'Ошибка', 'error');
                }
            } catch (err) {
                console.error('Comment error:', err);
                showToast('Ошибка отправки', 'error');
            }
        });
    }

    // === Жалоба ===
    const complaintForm = document.getElementById('complaintForm');
    if (complaintForm) {
        complaintForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const articleId = complaintForm.dataset.articleId;
            const formData = new FormData(complaintForm);
            formData.append('article_id', articleId);

            try {
                const res = await fetch(SITE_URL + '/api/complaint.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const data = await res.json();
                if (data.success) {
                    closeComplaintModal();
                    showToast(data.message, 'success');
                } else {
                    showToast(data.error || 'Ошибка', 'error');
                }
            } catch (err) {
                console.error('Complaint error:', err);
                showToast('Ошибка отправки', 'error');
            }
        });
    }
});

// SITE_URL — определяется глобально
const SITE_URL = document.querySelector('meta[name="site-url"]')?.content 
    || window.location.origin + '/techpulse';

// === Функции ===
function deleteComment(id) {
    if (!confirm('Удалить комментарий?')) return;

    fetch(SITE_URL + '/api/comment_delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'comment_id=' + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById('comment-' + id);
            if (el) {
                el.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => el.remove(), 300);
            }
            showToast('Комментарий удалён', 'success');
        }
    })
    .catch(err => console.error(err));
}

function openComplaintModal() {
    document.getElementById('complaintModal')?.classList.add('show');
}

function closeComplaintModal() {
    document.getElementById('complaintModal')?.classList.remove('show');
}

function showToast(message, type = 'success') {
    // Удалить предыдущий toast
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${escapeHtml(message)}</span><button class="toast-close" onclick="this.parentElement.remove()">✕</button>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}