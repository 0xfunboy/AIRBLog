document.addEventListener('DOMContentLoaded', function () {
    function qs(selector, scope) {
        var context = scope || document;
        return context ? context.querySelector(selector) : null;
    }

    function qsa(selector, scope) {
        var context = scope || document;
        if (!context) {
            return [];
        }
        var nodeList = context.querySelectorAll(selector);
        return Array.prototype.slice.call(nodeList);
    }

    function ensureToaster() {
        var container = qs('.admin-toaster');
        if (!container) {
            container = document.createElement('div');
            container.className = 'admin-toaster';
            document.body.appendChild(container);
        }
        return container;
    }

    var toaster = ensureToaster();

    function showToast(message, type) {
        if (!message) {
            return;
        }
        var toast = document.createElement('div');
        toast.className = 'admin-toast';
        if (type === 'error') {
            toast.classList.add('error');
        }
        toast.textContent = message;
        toaster.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('fade');
        }, 2600);
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3400);
    }

    function copyToClipboard(value, onSuccess, onError) {
        if (!value) {
            if (onSuccess) {
                onSuccess();
            }
            return;
        }

        function fallback() {
            var temp = document.createElement('textarea');
            temp.value = value;
            temp.style.position = 'fixed';
            temp.style.opacity = '0';
            document.body.appendChild(temp);
            temp.focus();
            temp.select();
            var succeeded = false;
            try {
                succeeded = document.execCommand('copy');
            } catch (err) {
                succeeded = false;
            }
            document.body.removeChild(temp);
            if (succeeded) {
                if (onSuccess) {
                    onSuccess();
                }
            } else if (onError) {
                onError(new Error('Copy command not supported.'));
            }
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value).then(function () {
                if (onSuccess) {
                    onSuccess();
                }
            }).catch(function () {
                fallback();
            });
            return;
        }

        fallback();
    }

    function flashCopyState(button, fallback) {
        if (!button) {
            return;
        }
        var original = button.textContent || fallback || 'Copy URL';
        button.textContent = 'Copied!';
        button.disabled = true;
        setTimeout(function () {
            button.textContent = original;
            button.disabled = false;
        }, 1400);
    }

    function bindCopyButton(button, getValue, fallbackLabel) {
        if (!button || button.getAttribute('data-copy-bound') === 'true') {
            return;
        }
        button.setAttribute('data-copy-bound', 'true');
        button.addEventListener('click', function () {
            try {
                var value = getValue();
                if (!value) {
                    return;
                }
                copyToClipboard(value, function () {
                    flashCopyState(button, fallbackLabel);
                    showToast('Media URL copied to clipboard.');
                }, function () {
                    showToast('Unable to copy URL.', 'error');
                });
            } catch (error) {
                console.error('Unable to copy media URL', error);
                showToast('Unable to copy URL.', 'error');
            }
        });
    }

    function formatBytes(bytes) {
        var value = Number(bytes);
        if (!value || value < 0) {
            return '0 B';
        }
        if (value < 1024) {
            return value + ' B';
        }
        if (value < 1024 * 1024) {
            return (value / 1024).toFixed(1) + ' KB';
        }
        return (value / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function formatDimensions(width, height) {
        var w = Number(width);
        var h = Number(height);
        if (isFinite(w) && isFinite(h) && w > 0 && h > 0) {
            return 'W ' + w + ' x H ' + h + ' px';
        }
        return 'W n/a x H n/a px';
    }

    function formatTimestamp(timestamp) {
        var value = Number(timestamp);
        if (!value || value <= 0) {
            return 'Unknown';
        }
        var date = new Date(value * 1000);
        if (isNaN(date.getTime())) {
            return 'Unknown';
        }
        function pad(input) {
            return String(input).length === 1 ? '0' + String(input) : String(input);
        }
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
            ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function toNumber(value, fallback) {
        var number = Number(value);
        return isFinite(number) ? number : (typeof fallback === 'number' ? fallback : 0);
    }

    function safeMessage(value, fallback) {
        if (typeof value === 'string' && value.trim()) {
            return value.trim();
        }
        return typeof fallback === 'string' ? fallback : '';
    }

    function isImageType(type) {
        var value = (type || '').toLowerCase();
        return value === 'png' || value === 'jpg' || value === 'jpeg' || value === 'webp' || value === 'svg' || value === 'gif' || value === 'ico';
    }

    function initToggles() {
        qsa('[data-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var id = toggle.getAttribute('data-toggle') || '';
                var target = document.getElementById(id);
                if (target) {
                    if (target.classList.contains('hidden')) {
                        target.classList.remove('hidden');
                    } else {
                        target.classList.add('hidden');
                    }
                }
            });
        });
    }

    function toSlug(value) {
        return (value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .substring(0, 150);
    }

    function initSlugSync() {
        qsa('[data-slug-source]').forEach(function (input) {
            var targetId = input.getAttribute('data-slug-source');
            if (!targetId) {
                return;
            }
            var target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            function maybeSync() {
                if (target.dataset.slugDirty === 'true') {
                    return;
                }
                target.value = toSlug(input.value);
            }

            if (!target.value) {
                maybeSync();
            } else {
                target.dataset.slugDirty = 'true';
            }

            input.addEventListener('input', maybeSync);
            target.addEventListener('input', function () {
                target.dataset.slugDirty = target.value ? 'true' : '';
            });
        });
    }

    function attachRemoveHandler(scope) {
        qsa('[data-repeat-remove]', scope).forEach(function (button) {
            button.addEventListener('click', function () {
                var item = button.closest('[data-repeat-item]');
                if (item && item.parentElement && item.parentElement.children.length > 1) {
                    item.parentElement.removeChild(item);
                } else if (item) {
                    qsa('input, textarea', item).forEach(function (field) {
                        field.value = '';
                    });
                }
            });
        });
    }

    function initRepeaters() {
        qsa('[data-repeat-add]').forEach(function (button) {
            var root = button.closest('[data-repeat-root]');
            if (!root) {
                return;
            }
            var container = qs('[data-repeat-container]', root);
            if (!container) {
                return;
            }
            var templateId = button.getAttribute('data-repeat-template');
            var template = templateId ? document.getElementById(templateId) : null;
            if (!template || !(template instanceof HTMLTemplateElement)) {
                return;
            }

            var name = button.getAttribute('data-repeat-name') || 'items';

            function initIndex() {
                var selector = '[name^="' + name + '["]';
                var fields = qsa(selector, container);
                var max = 0;
                fields.forEach(function (field) {
                    var nameAttr = field.getAttribute('name') || '';
                    var match = nameAttr.match(/\[(\d+)\]/);
                    if (match) {
                        var value = parseInt(match[1], 10);
                        if (!isNaN(value)) {
                            if (value + 1 > max) {
                                max = value + 1;
                            }
                        }
                    }
                });
                container.dataset.repeatIndex = String(max);
            }

            if (!container.dataset.repeatIndex) {
                initIndex();
            }

            button.addEventListener('click', function () {
                var index = parseInt(container.dataset.repeatIndex || '0', 10);
                container.dataset.repeatIndex = String(index + 1);
                var fragment = template.content.cloneNode(true);
                qsa('[data-repeat-field]', fragment).forEach(function (field) {
                    var key = field.getAttribute('data-repeat-field');
                    if (!key) {
                        return;
                    }
                    field.setAttribute('name', name + '[' + index + '][' + key + ']');
                    if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                        field.value = '';
                    }
                });
                container.appendChild(fragment);
                attachRemoveHandler(container);
            });

            attachRemoveHandler(container);
        });
    }

    function initCopyButtons(scope) {
        var context = scope || document;
        qsa('[data-copy-url]', context).forEach(function (button) {
            bindCopyButton(button, function () {
                var raw = button.getAttribute('data-copy-url') || '';
                if (!raw) {
                    return '';
                }
                if (raw.indexOf('http://') === 0 || raw.indexOf('https://') === 0) {
                    return raw;
                }
                return window.location.origin + raw;
            }, 'Copy URL');
        });

        qsa('[data-media-copy]', context).forEach(function (button) {
            var wrapper = button.closest('[data-media-input]');
            var input = wrapper ? qs('[data-media-url]', wrapper) : null;
            bindCopyButton(button, function () {
                if (!input || !input.value) {
                    return '';
                }
                var raw = input.value;
                if (raw.indexOf('http://') === 0 || raw.indexOf('https://') === 0) {
                    return raw;
                }
                return window.location.origin + raw;
            }, 'Copy URL');
        });
    }

    function getJson(url, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                var data = null;
                try {
                    data = xhr.responseText ? JSON.parse(xhr.responseText) : {};
                } catch (err) {
                    data = null;
                }
                if (xhr.status >= 200 && xhr.status < 300) {
                    callback(null, data || {});
                } else {
                    callback(data || { error: 'Request failed', status: xhr.status });
                }
            }
        };
        xhr.send(null);
    }

    function parseJsonSafely(text) {
        if (!text) {
            return {};
        }
        try {
            return JSON.parse(text);
        } catch (err) {
            return null;
        }
    }

    function postForm(url, formData, callback) {
        if (window.fetch) {
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
                body: formData
            }).then(function (response) {
                return response.text().then(function (text) {
                    var data = parseJsonSafely(text);
                    if (response.ok) {
                        callback(null, data || {});
                    } else {
                        callback(data || { error: 'Request failed', status: response.status });
                    }
                });
            }).catch(function (error) {
                callback({ error: error && error.message ? error.message : 'Network error' });
            });
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.withCredentials = true;
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                var data = parseJsonSafely(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300) {
                    callback(null, data || {});
                } else {
                    callback(data || { error: 'Request failed', status: xhr.status });
                }
            }
        };
        xhr.send(formData);
    }

    function postJson(url, payload, callback) {
        if (window.fetch) {
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(function (response) {
                return response.text().then(function (text) {
                    var data = parseJsonSafely(text);
                    if (response.ok) {
                        callback(null, data || {});
                    } else {
                        callback(data || { error: 'Request failed', status: response.status });
                    }
                });
            }).catch(function (error) {
                callback({ error: error && error.message ? error.message : 'Network error' });
            });
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.withCredentials = true;
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                var data = parseJsonSafely(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300) {
                    callback(null, data || {});
                } else {
                    callback(data || { error: 'Request failed', status: xhr.status });
                }
            }
        };
        xhr.send(JSON.stringify(payload));
    }

    function MediaPicker(options) {
        options = options || {};
        this.fetchMedia = options.fetchMedia;
        this.media = [];
        this.modal = null;
        this.listEl = null;
        this.loadingEl = null;
        this.searchInput = null;
        this.filterSelect = null;
        this.onSelect = null;
    }

    MediaPicker.prototype.open = function (config) {
        var self = this;
        config = config || {};
        this.onSelect = config.onSelect || null;
        if (!this.modal) {
            this.buildModal();
        }
        this.modal.classList.remove('hidden');
        document.body.classList.add('admin-modal-open');
        this.load(true, function () {
            self.updateList();
        });
    };

    MediaPicker.prototype.close = function () {
        if (this.modal) {
            this.modal.classList.add('hidden');
        }
        document.body.classList.remove('admin-modal-open');
        this.onSelect = null;
    };

    MediaPicker.prototype.load = function (force, done) {
        var self = this;
        var shouldForce = !!force;
        if (!shouldForce && this.media.length > 0) {
            if (done) {
                done();
            }
            return;
        }
        if (this.loadingEl) {
            this.loadingEl.classList.remove('hidden');
        }
        if (typeof this.fetchMedia !== 'function') {
            this.media = [];
            if (this.loadingEl) {
                this.loadingEl.classList.add('hidden');
            }
            if (done) {
                done();
            }
            return;
        }
        this.fetchMedia(function (error, media) {
            if (error) {
                console.error('Unable to load media list', error);
                showToast((error && error.error) ? error.error : 'Unable to load media list.', 'error');
                self.media = [];
            } else {
                self.media = Array.isArray(media) ? media.slice() : [];
                self.updateFilterOptions();
            }
            if (self.loadingEl) {
                self.loadingEl.classList.add('hidden');
            }
            if (done) {
                done();
            }
        });
    };

    MediaPicker.prototype.setMedia = function (media) {
        this.media = Array.isArray(media) ? media.slice() : [];
        this.updateFilterOptions();
        if (this.modal && !this.modal.classList.contains('hidden')) {
            this.updateList();
        }
    };

    MediaPicker.prototype.updateFilterOptions = function () {
        if (!this.filterSelect) {
            return;
        }
        var previous = this.filterSelect.value || 'all';
        var seen = {};
        var types = [];
        for (var i = 0; i < this.media.length; i += 1) {
            var type = (this.media[i].type || '').toLowerCase();
            if (type && !seen[type]) {
                seen[type] = true;
                types.push(type);
            }
        }
        types.sort();
        this.filterSelect.innerHTML = '';

        var allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = 'All types';
        this.filterSelect.appendChild(allOption);

        for (var j = 0; j < types.length; j += 1) {
            var option = document.createElement('option');
            option.value = types[j];
            option.textContent = '.' + types[j].toUpperCase();
            this.filterSelect.appendChild(option);
        }

        if (seen[previous]) {
            this.filterSelect.value = previous;
        } else {
            this.filterSelect.value = 'all';
        }
    };

    MediaPicker.prototype.buildModal = function () {
        var self = this;
        var overlay = document.createElement('div');
        overlay.className = 'admin-modal media-picker hidden';

        var content = document.createElement('div');
        content.className = 'admin-modal__content media-picker__content';
        overlay.appendChild(content);

        var header = document.createElement('div');
        header.className = 'media-picker__header';

        var title = document.createElement('h2');
        title.className = 'media-picker__title';
        title.textContent = 'Select media';
        header.appendChild(title);

        var controls = document.createElement('div');
        controls.className = 'media-picker__controls';

        this.searchInput = document.createElement('input');
        this.searchInput.type = 'search';
        this.searchInput.placeholder = 'Search media…';
        this.searchInput.className = 'media-picker__search';
        controls.appendChild(this.searchInput);

        this.filterSelect = document.createElement('select');
        this.filterSelect.className = 'media-picker__filter';
        controls.appendChild(this.filterSelect);

        header.appendChild(controls);
        content.appendChild(header);

        this.loadingEl = document.createElement('div');
        this.loadingEl.className = 'media-picker__loading hidden';
        this.loadingEl.textContent = 'Loading media…';
        content.appendChild(this.loadingEl);

        this.listEl = document.createElement('div');
        this.listEl.className = 'media-picker__grid';
        content.appendChild(this.listEl);

        var footer = document.createElement('div');
        footer.className = 'media-picker__footer';

        var cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'media-picker__button';
        cancelButton.textContent = 'Cancel';
        footer.appendChild(cancelButton);

        content.appendChild(footer);

        document.body.appendChild(overlay);
        this.modal = overlay;

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                self.close();
            }
        });

        cancelButton.addEventListener('click', function () {
            self.close();
        });

        if (this.searchInput) {
            this.searchInput.addEventListener('input', function () {
                self.updateList();
            });
        }
        if (this.filterSelect) {
            this.filterSelect.addEventListener('change', function () {
                self.updateList();
            });
        }
    };

    MediaPicker.prototype.filterMedia = function () {
        var term = '';
        if (this.searchInput && this.searchInput.value) {
            term = this.searchInput.value.toLowerCase().trim();
        }
        var filter = this.filterSelect ? this.filterSelect.value : 'all';
        var results = [];
        for (var i = 0; i < this.media.length; i += 1) {
            var item = this.media[i];
            var typeMatch = filter === 'all' || (item.type || '').toLowerCase() === filter;
            if (!typeMatch) {
                continue;
            }
            if (!term) {
                results.push(item);
                continue;
            }
            var haystack = ((item.path || '') + ' ' + (item.url || '')).toLowerCase();
            if (haystack.indexOf(term) !== -1) {
                results.push(item);
            }
        }
        return results;
    };

    MediaPicker.prototype.updateList = function () {
        if (!this.listEl) {
            return;
        }
        this.listEl.innerHTML = '';
        var items = this.filterMedia();
        if (!items.length) {
            var empty = document.createElement('p');
            empty.className = 'media-picker__empty';
            empty.textContent = 'No media found.';
            this.listEl.appendChild(empty);
            return;
        }

        var self = this;
        for (var i = 0; i < items.length; i += 1) {
            var item = items[i];
            var card = document.createElement('div');
            card.className = 'media-picker__item';

            var thumb = document.createElement('div');
            thumb.className = 'media-picker__thumb';
            if (isImageType(item.type)) {
                var img = document.createElement('img');
                img.src = item.url;
                img.alt = item.path || 'Media preview';
                thumb.appendChild(img);
            } else {
                var badge = document.createElement('span');
                badge.className = 'media-picker__type';
                badge.textContent = (item.type || '').toUpperCase();
                thumb.appendChild(badge);
            }
            card.appendChild(thumb);

            var details = document.createElement('div');
            details.className = 'media-picker__details';

            var name = document.createElement('p');
            name.className = 'media-picker__name';
            name.textContent = item.path || '';
            details.appendChild(name);

            var meta = document.createElement('p');
            meta.className = 'media-picker__meta';
            meta.textContent = formatDimensions(item.width, item.height) + ' · ' + formatBytes(item.size || 0);
            details.appendChild(meta);

            var badgeStatus = document.createElement('span');
            badgeStatus.className = 'media-picker__badge ' + (item.in_use ? 'is-used' : 'is-free');
            badgeStatus.textContent = item.in_use ? 'In use' : 'Not in use';
            details.appendChild(badgeStatus);

            card.appendChild(details);

            var actions = document.createElement('div');
            actions.className = 'media-picker__actions';

            var selectButton = document.createElement('button');
            selectButton.type = 'button';
            selectButton.className = 'media-picker__select';
            selectButton.textContent = 'Select';
            (function (mediaItem) {
                selectButton.addEventListener('click', function () {
                    if (typeof self.onSelect === 'function') {
                        var path = mediaItem.path || '';
                        self.onSelect({
                            path: path,
                            url: mediaItem.url || (path ? '/' + path.replace(/^\/+/, '') : ''),
                            width: mediaItem.width,
                            height: mediaItem.height,
                            size: mediaItem.size,
                            type: mediaItem.type,
                            in_use: mediaItem.in_use
                        });
                    }
                    self.close();
                });
            })(item);

            actions.appendChild(selectButton);
            card.appendChild(actions);

            this.listEl.appendChild(card);
        }
    };

    function initMediaInputs(mediaPicker) {
        function makeMediaUrl(value) {
            if (!value) {
                return '';
            }
            var trimmed = String(value).trim();
            if (!trimmed) {
                return '';
            }
            if (/^https?:\/\//i.test(trimmed) || trimmed.indexOf('/') === 0) {
                return trimmed;
            }
            return '/media/' + trimmed.replace(/^\/+/, '');
        }

        qsa('[data-media-input]').forEach(function (wrapper) {
            var urlInput = qs('[data-media-url]', wrapper);
            var fileInput = qs('[data-media-file]', wrapper);
            var preview = qs('[data-media-preview]', wrapper);
            var placeholder = qs('[data-media-placeholder]', wrapper);
            var link = qs('[data-media-link]', wrapper);
            var uploadLabel = qs('[data-media-upload-label]', wrapper);
            var uploadButton = qs('[data-media-upload]', wrapper);
            var selectButton = qs('[data-media-select]', wrapper);
            var objectUrl = null;

            function disableLink() {
                if (!link) {
                    return;
                }
                link.href = '#';
                link.textContent = 'No file selected';
                link.classList.remove('text-cy');
                link.classList.remove('hover:underline');
                link.classList.add('text-muted');
                link.classList.add('pointer-events-none');
                link.setAttribute('aria-disabled', 'true');
            }

            function enableLink(href, label) {
                if (!link) {
                    return;
                }
                link.href = href;
                link.textContent = label;
                link.classList.add('text-cy');
                link.classList.add('hover:underline');
                link.classList.remove('text-muted');
                link.classList.remove('pointer-events-none');
                link.removeAttribute('aria-disabled');
            }

            function applyPreview(src, pending) {
                if (objectUrl && window.URL && window.URL.revokeObjectURL) {
                    window.URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }
                if (pending && src && String(src).indexOf('blob:') === 0) {
                    objectUrl = src;
                }
                if (preview) {
                    if (src) {
                        preview.src = src;
                        preview.classList.remove('hidden');
                        if (placeholder) {
                            placeholder.classList.add('hidden');
                        }
                    } else {
                        preview.src = '';
                        preview.classList.add('hidden');
                        if (placeholder) {
                            placeholder.classList.remove('hidden');
                        }
                    }
                }
                if (uploadLabel) {
                    if (pending) {
                        uploadLabel.classList.remove('hidden');
                    } else {
                        uploadLabel.classList.add('hidden');
                    }
                }
                if (src) {
                    enableLink(src, pending ? 'Preview upload' : 'Open current');
                } else {
                    disableLink();
                }
            }

            function applyUrlValue(value) {
                if (urlInput) {
                    urlInput.value = value;
                }
                var normalized = makeMediaUrl(value);
                applyPreview(normalized, false);
            }

            if (!preview || (preview instanceof HTMLImageElement && !preview.src)) {
                var initialUrl = urlInput ? makeMediaUrl(urlInput.value) : '';
                if (initialUrl) {
                    applyPreview(initialUrl, false);
                } else {
                    applyPreview('', false);
                }
            }

            if (uploadButton && fileInput) {
                uploadButton.addEventListener('click', function () {
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    var file = fileInput.files && fileInput.files[0];
                    if (file) {
                        var blobUrl = (window.URL && window.URL.createObjectURL) ? window.URL.createObjectURL(file) : null;
                        applyPreview(blobUrl || '', true);
                    } else {
                        var fallback = urlInput ? makeMediaUrl(urlInput.value) : '';
                        applyPreview(fallback, false);
                    }
                });
            }

            if (selectButton) {
                selectButton.addEventListener('click', function () {
                    if (!mediaPicker) {
                        return;
                    }
                    mediaPicker.open({
                        onSelect: function (item) {
                            var newValue = item.path ? (item.path.indexOf('/') === 0 ? item.path : '/' + item.path.replace(/^\/+/, '')) : '';
                            if (fileInput) {
                                fileInput.value = '';
                            }
                            if (uploadLabel) {
                                uploadLabel.classList.add('hidden');
                            }
                            applyUrlValue(newValue);
                        }
                    });
                });
            }

            disableLink();
            initCopyButtons(wrapper);
        });
    }

    function initMediaLibrary(libraryEl, fetchMediaFn, csrfToken, mediaPicker) {
        if (!libraryEl) {
            return {
                refresh: function (next) {
                    if (typeof next === 'function') {
                        next([]);
                    }
                }
            };
        }

        var gridEl = qs('[data-media-grid]', libraryEl);
        var emptyEl = qs('[data-media-empty]', libraryEl);

        function buildMediaCard(item) {
            var card = document.createElement('article');
            card.className = 'media-card card space-y-3';
            card.setAttribute('data-media-card', '');
            card.setAttribute('data-media-path', item.path || '');

            var thumb = document.createElement('div');
            thumb.className = 'media-card__thumb bg-bg2 border border-stroke rounded-lg overflow-hidden aspect-video flex items-center justify-center';
            if (isImageType(item.type)) {
                var img = document.createElement('img');
                img.src = item.url;
                img.alt = item.path || 'Media preview';
                img.className = 'max-h-full max-w-full object-contain media-card__image';
                thumb.appendChild(img);
            } else {
                var span = document.createElement('span');
                span.className = 'text-xs text-muted uppercase tracking-wide';
                span.textContent = (item.type || '').toUpperCase();
                thumb.appendChild(span);
            }
            card.appendChild(thumb);

            var body = document.createElement('div');
            body.className = 'space-y-2';

            var pathEl = document.createElement('p');
            pathEl.className = 'media-card__path text-sm font-semibold text-acc break-all';
            pathEl.textContent = item.path || '';
            body.appendChild(pathEl);

            var meta = document.createElement('div');
            meta.className = 'media-card__meta text-xs text-muted space-y-1';

            var dims = document.createElement('p');
            dims.className = 'media-card__dimensions';
            dims.textContent = formatDimensions(item.width, item.height);
            meta.appendChild(dims);

            var sizeLine = document.createElement('p');
            var sizeSpan = document.createElement('span');
            sizeSpan.className = 'media-card__filesize';
            sizeSpan.textContent = formatBytes(item.size || 0);
            sizeLine.appendChild(sizeSpan);
            sizeLine.appendChild(document.createTextNode(' · '));
            var updatedSpan = document.createElement('span');
            updatedSpan.className = 'media-card__updated';
            updatedSpan.textContent = 'Updated ' + formatTimestamp(item.modified);
            sizeLine.appendChild(updatedSpan);
            meta.appendChild(sizeLine);

            body.appendChild(meta);

            var statusRow = document.createElement('div');
            statusRow.className = 'media-card__status flex items-center gap-2 text-xs';
            var badge = document.createElement('span');
            badge.className = 'media-card__badge ' + (item.in_use ? 'media-card__badge--used' : 'media-card__badge--unused');
            var dot = document.createElement('span');
            dot.className = 'media-card__badge-dot';
            badge.appendChild(dot);
            var label = document.createElement('span');
            label.textContent = item.in_use ? 'In use' : 'Not in use';
            badge.appendChild(label);
            statusRow.appendChild(badge);
            body.appendChild(statusRow);

            card.appendChild(body);

            if (item.variants) {
                var variantsKeys = Object.keys(item.variants);
                if (variantsKeys.length) {
                    var variantsWrap = document.createElement('div');
                    variantsWrap.className = 'flex flex-wrap gap-2 text-xs media-card__variants';
                    variantsKeys.forEach(function (variantType) {
                        var variant = item.variants[variantType];
                        var pill = document.createElement('div');
                        pill.className = 'media-variant-pill';

                        var labelEl = document.createElement('span');
                        labelEl.className = 'media-variant-label';
                        labelEl.textContent = variantType.toUpperCase();
                        pill.appendChild(labelEl);

                        var sizeEl = document.createElement('span');
                        sizeEl.className = 'media-variant-size';
                        sizeEl.textContent = formatBytes(variant.size || 0);
                        pill.appendChild(sizeEl);

                        var openEl = document.createElement('a');
                        openEl.className = 'media-variant-open';
                        openEl.href = variant.url;
                        openEl.target = '_blank';
                        openEl.rel = 'noopener';
                        openEl.textContent = 'Open';
                        pill.appendChild(openEl);

                        var copyEl = document.createElement('button');
                        copyEl.type = 'button';
                        copyEl.className = 'media-variant-copy';
                        copyEl.setAttribute('data-copy-url', variant.url);
                        copyEl.textContent = 'Copy';
                        pill.appendChild(copyEl);

                        variantsWrap.appendChild(pill);
                    });
                    card.appendChild(variantsWrap);
                }
            }

            var actions = document.createElement('div');
            actions.className = 'media-card__actions flex flex-wrap items-center gap-2 text-sm';

            var openLink = document.createElement('a');
            openLink.href = item.url;
            openLink.target = '_blank';
            openLink.rel = 'noopener';
            openLink.className = 'media-card__action';
            openLink.textContent = 'Open';
            actions.appendChild(openLink);

            var copyButton = document.createElement('button');
            copyButton.type = 'button';
            copyButton.className = 'media-card__action';
            copyButton.setAttribute('data-copy-url', item.url);
            copyButton.textContent = 'Copy URL';
            actions.appendChild(copyButton);

            var replaceButton = document.createElement('button');
            replaceButton.type = 'button';
            replaceButton.className = 'media-card__action';
            replaceButton.setAttribute('data-media-replace', '');
            replaceButton.textContent = 'Replace';
            actions.appendChild(replaceButton);

            var deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'media-card__action danger';
            deleteButton.setAttribute('data-media-delete', '');
            deleteButton.textContent = 'Delete';
            actions.appendChild(deleteButton);

            var replaceInput = document.createElement('input');
            replaceInput.type = 'file';
            replaceInput.accept = '.png,.jpg,.jpeg,.webp,.svg,.ico';
            replaceInput.className = 'hidden';
            replaceInput.setAttribute('data-media-replace-input', '');
            actions.appendChild(replaceInput);

            card.appendChild(actions);

            bindCardActions(card, item);
            initCopyButtons(card);
            return card;
        }

        function bindCardActions(card, item) {
            var replaceButton = qs('[data-media-replace]', card);
            var replaceInput = qs('[data-media-replace-input]', card);
            var deleteButton = qs('[data-media-delete]', card);

            if (replaceButton && replaceInput) {
                replaceButton.addEventListener('click', function () {
                    replaceInput.click();
                });
                replaceInput.addEventListener('change', function () {
                    var file = replaceInput.files && replaceInput.files[0];
                    if (!file) {
                        return;
                    }
                    card.classList.add('media-card--loading');
                    var formData = new FormData();
                    formData.append('csrf_token', csrfToken);
                    formData.append('path', item.path || '');
                    formData.append('file', file, file.name);
                    postForm('/admin/media/replace', formData, function (error, data) {
                        replaceInput.value = '';
                        card.classList.remove('media-card--loading');
                        if (error) {
                            var message = (error && error.error) ? error.error : 'Unable to replace media.';
                            showToast(message, 'error');
                            console.error('Replace failed', error);
                            return;
                        }
                        showToast(data.message || 'Media replaced.');
                        refresh();
                    });
                });
            }

            if (deleteButton) {
                deleteButton.addEventListener('click', function () {
                    var confirmed = window.confirm('Delete this media file permanently? This can’t be undone.');
                    if (!confirmed) {
                        return;
                    }
                    card.classList.add('media-card--loading');
                    var formData = new FormData();
                    formData.append('csrf_token', csrfToken);
                    formData.append('path', item.path || '');
                    postForm('/admin/media/delete', formData, function (error, data) {
                        card.classList.remove('media-card--loading');
                        if (error) {
                            var message = (error && error.error) ? error.error : 'Unable to delete media.';
                            showToast(message, 'error');
                            console.error('Delete failed', error);
                            return;
                        }
                        showToast(data.message || 'Media deleted.');
                        refresh();
                    });
                });
            }
        }

        function renderMediaGrid(items) {
            if (!gridEl || !emptyEl) {
                return;
            }
            if (!items.length) {
                gridEl.innerHTML = '';
                gridEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                return;
            }
            emptyEl.classList.add('hidden');
            gridEl.classList.remove('hidden');
            gridEl.innerHTML = '';
            for (var i = 0; i < items.length; i += 1) {
                var card = buildMediaCard(items[i]);
                gridEl.appendChild(card);
            }
        }

        function refresh(next) {
            fetchMediaFn(function (error, payload) {
                if (error) {
                    console.error('Media listing failed', error);
                    showToast((error && error.error) ? error.error : 'Unable to load media list.', 'error');
                    renderMediaGrid([]);
                    if (typeof next === 'function') {
                        next([]);
                    }
                    return;
                }
                var media = Array.isArray(payload) ? payload : (payload.media || []);
                renderMediaGrid(media);
                if (mediaPicker && typeof mediaPicker.setMedia === 'function') {
                    mediaPicker.setMedia(media);
                }
                if (typeof next === 'function') {
                    next(media);
                }
            });
        }

        qsa('[data-media-card]', gridEl).forEach(function (card) {
            var path = card.getAttribute('data-media-path') || '';
            var item = {
                path: path,
                url: card.getAttribute('data-media-url') || '',
                width: parseInt(card.getAttribute('data-media-width') || '', 10) || null,
                height: parseInt(card.getAttribute('data-media-height') || '', 10) || null,
                in_use: card.getAttribute('data-media-in-use') === '1'
            };
            bindCardActions(card, item);
            initCopyButtons(card);
        });

        return {
            refresh: refresh
        };
    }

    function initMediaTools(mediaLibrary) {
        var mediaTools = qs('[data-media-tools]');
        if (!mediaTools) {
            return;
        }

        var statusBox = qs('[data-media-status]', mediaTools);
        var summary = qs('[data-media-summary]', mediaTools);
        var logList = qs('[data-media-log]', mediaTools);

        qsa('form[data-media-action]', mediaTools).forEach(function (form) {
            var action = form.getAttribute('data-media-action') || 'optimize';
            var button = qs('button[type="submit"]', form);
            var fileInput = qs('input[type="file"]', form);

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (action === 'upload' && (!fileInput || !fileInput.files || fileInput.files.length === 0)) {
                    if (summary) {
                        summary.textContent = 'Select a file to upload.';
                    }
                    return;
                }

                if (button) {
                    button.disabled = true;
                    button.dataset.originalLabel = button.textContent || '';
                    if (action === 'mirror') {
                        button.textContent = 'Mirroring…';
                    } else if (action === 'upload') {
                        button.textContent = 'Uploading…';
                    } else {
                        button.textContent = 'Optimizing…';
                    }
                }

                if (statusBox) {
                    statusBox.classList.remove('hidden');
                }
                if (summary) {
                    if (action === 'mirror') {
                        summary.textContent = 'Phase 1 – mirroring remote assets…';
                    } else if (action === 'upload') {
                        summary.textContent = 'Uploading media…';
                    } else {
                        summary.textContent = 'Phase 2 – converting images to WebP…';
                    }
                }
                if (logList) {
                    logList.innerHTML = '';
                }

                var formData = new FormData(form);
                var targetUrl = form.getAttribute('action') || '/admin/media/optimize';

                postForm(targetUrl, formData, function (error, data) {
                    if (button) {
                        button.disabled = false;
                        button.textContent = button.dataset.originalLabel || button.textContent;
                        delete button.dataset.originalLabel;
                    }
                    if (fileInput) {
                        fileInput.value = '';
                    }

                    if (error) {
                        var message = (error && error.error) ? error.error : 'Unexpected error while processing media.';
                        if (summary) {
                            summary.textContent = message;
                        }
                        showToast(message, 'error');
                        return;
                    }

                    var payload = (data && typeof data === 'object') ? data : {};
                    if (payload.ok === false) {
                        var failureMessage = safeMessage(payload.error || payload.message, 'Operation failed.');
                        if (summary) {
                            summary.textContent = failureMessage;
                        }
                        showToast(failureMessage, 'error');
                        return;
                    }

                    if (logList) {
                        var steps = Array.isArray(payload.steps) ? payload.steps : [];
                        logList.innerHTML = '';
                        steps.forEach(function (step) {
                            var item = document.createElement('li');
                            item.textContent = 'Phase ' + step.phase + ': ' + step.message + ' (' + step.current + '/' + step.total + ')';
                            if (step.status === 'error') {
                                item.classList.add('media-optimize-status__item--error');
                            } else if (step.status === 'skip') {
                                item.classList.add('media-optimize-status__item--skip');
                            }
                            logList.appendChild(item);
                        });
                    }

                    if (summary) {
                        var processed = toNumber(payload.processed, 0);
                        var total = toNumber(payload.total, 0);
                        var errorCount = toNumber(payload.errors, 0);
                        var fallbackMessage;
                        if (action === 'mirror') {
                            fallbackMessage = 'Mirrored ' + processed + '/' + total + ' assets (errors: ' + errorCount + ').';
                        } else if (action === 'upload') {
                            fallbackMessage = 'Upload complete.';
                        } else {
                            fallbackMessage = 'Converted ' + processed + '/' + total + ' files to WebP (errors: ' + errorCount + ').';
                        }
                        summary.textContent = safeMessage(payload.message, fallbackMessage);
                    }

                    var toastMessage = safeMessage(payload.message, action === 'upload' ? 'Upload complete.' : 'Media task finished.');
                    showToast(toastMessage);
                    if (mediaLibrary && typeof mediaLibrary.refresh === 'function') {
                        mediaLibrary.refresh();
                    }
                });
            });
        });
    }

    initToggles();
    initSlugSync();
    initRepeaters();

    var mediaLibraryEl = qs('[data-media-library]');
    var csrfToken = '';
    if (mediaLibraryEl && mediaLibraryEl.getAttribute) {
        csrfToken = mediaLibraryEl.getAttribute('data-csrf-token') || '';
    }

    var mediaPicker = new MediaPicker({
        fetchMedia: function (callback) {
            getJson('/admin/media/list?format=json', function (error, data) {
                if (error) {
                    callback(error);
                    return;
                }
                callback(null, data.media || []);
            });
        }
    });

    initMediaInputs(mediaPicker);

    var mediaLibrary = initMediaLibrary(
        mediaLibraryEl,
        function (callback) {
            getJson('/admin/media/list?format=json', function (error, data) {
                if (error) {
                    callback(error);
                    return;
                }
                callback(null, data.media || []);
            });
        },
        csrfToken,
        mediaPicker
    );

    initMediaTools(mediaLibrary);
    initCopyButtons(document);

    function initInlineEditing(context) {
        if (!context || !context.endpoints) {
            return;
        }

        var inlineState = {
            enabled: Boolean(context.enabled),
            csrf: context.csrf || '',
            initialized: false
        };
        var endpoints = context.endpoints || {};
        var bodyElement = document.body;
        var htmlModal = null;
        var logoutModal = null;

        function updateInlineCsrf(token) {
            inlineState.csrf = token || '';
            var meta = qs('meta[name="csrf-token"]');
            if (!meta) {
                meta = document.createElement('meta');
                meta.setAttribute('name', 'csrf-token');
                document.head.appendChild(meta);
            }
            meta.setAttribute('content', inlineState.csrf);
        }

        function getInlineCsrf() {
            if (!inlineState.csrf) {
                var meta = qs('meta[name="csrf-token"]');
                inlineState.csrf = meta ? meta.getAttribute('content') || '' : '';
            }
            return inlineState.csrf;
        }

        function toggleInlineControls(show) {
            qsa('[data-admin-controls]').forEach(function (wrapper) {
                if (show) {
                    wrapper.classList.remove('admin-hidden');
                } else {
                    wrapper.classList.add('admin-hidden');
                }
            });
        }

        function updateToolbarStatus(enabled) {
            var toolbar = qs('[data-admin-toolbar]');
            if (!toolbar) {
                return;
            }
            toolbar.setAttribute('data-enabled', enabled ? 'true' : 'false');

            var status = qs('[data-admin-status]');
            if (status) {
                status.textContent = enabled ? 'Inline editing enabled' : 'Inline editing disabled';
            }

            var toggle = qs('[data-admin-toggle]');
            if (toggle) {
                toggle.textContent = enabled ? 'Disable Admin Mode' : 'Enable Admin Mode';
            }
        }

        function cancelAllEdits() {
            qsa('[data-admin-editing="true"]').forEach(function (el) {
                el.dataset.adminEditing = 'false';
                el.removeAttribute('contenteditable');
                if (el.dataset.adminOriginal !== undefined) {
                    if (el.dataset.fieldType === 'html') {
                        el.innerHTML = el.dataset.adminOriginal;
                    } else {
                        el.textContent = el.dataset.adminOriginal;
                    }
                }
                el.classList.remove('admin-editing-outline');
            });
        }

        function inferType(el) {
            if (!el) {
                return 'text';
            }
            if (el.dataset && el.dataset.fieldType) {
                return el.dataset.fieldType;
            }
            if (el.tagName === 'IMG') {
                return 'image';
            }
            if (el.tagName === 'A') {
                return 'url';
            }
            return 'text';
        }

        function createActionButton(label, handler) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'admin-action-button';
            btn.textContent = label;
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                handler();
            });
            return btn;
        }

        function findControls(el) {
            var overlay = el.closest('.admin-edit-overlay');
            if (!overlay) {
                return null;
            }
            return qs('[data-admin-controls]', overlay);
        }

        function toggleButtons(el, visibilityMap) {
            var controls = findControls(el);
            if (!controls) {
                return;
            }
            var buttons = qsa('.admin-action-button', controls);
            buttons.forEach(function (btn) {
                var label = (btn.textContent || '').toLowerCase();
                if (label.indexOf('edit') !== -1) {
                    btn.classList.toggle('admin-hidden', !visibilityMap.edit);
                } else if (label.indexOf('save') !== -1) {
                    btn.classList.toggle('admin-hidden', !visibilityMap.save);
                } else if (label.indexOf('cancel') !== -1) {
                    btn.classList.toggle('admin-hidden', !visibilityMap.cancel);
                }
            });
        }

        function buildPayload(el, value) {
            return {
                model: el.dataset.model,
                key: el.dataset.key,
                id: el.dataset.id || null,
                value: value,
                csrf: getInlineCsrf()
            };
        }

        function ensureImageElement(element, wrapper) {
            if (!element) {
                return null;
            }
            if (element.tagName === 'IMG') {
                element.dataset.fieldType = 'image';
                element.dataset.adminSetup = 'true';
                return element;
            }

            var img = document.createElement('img');
            img.className = element.className || 'h-9 w-auto';
            var attrs = element.attributes;
            for (var i = 0; i < attrs.length; i += 1) {
                var attr = attrs[i];
                if (attr && attr.name && attr.name.indexOf('data-') === 0) {
                    img.setAttribute(attr.name, attr.value);
                }
            }
            img.dataset.fieldType = 'image';
            img.dataset.adminSetup = 'true';
            var alt = element.getAttribute('alt') || element.getAttribute('data-alt') || (element.textContent || '').trim();
            img.alt = alt || 'Site image';

            if (element.parentNode) {
                element.parentNode.replaceChild(img, element);
            } else if (wrapper) {
                wrapper.insertBefore(img, wrapper.firstChild);
            }

            return img;
        }

        function triggerImageReplace(element, wrapper) {
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = '.png,.jpg,.jpeg,.webp,.svg,.ico';
            input.addEventListener('change', function () {
                if (!input.files || !input.files[0]) {
                    return;
                }
                var target = ensureImageElement(element, wrapper);
                if (!target) {
                    showToast('Unable to determine image target.', 'error');
                    return;
                }
                var formData = new FormData();
                formData.append('file', input.files[0]);
                formData.append('model', target.dataset.model || '');
                formData.append('key', target.dataset.key || '');
                if (target.dataset.id) {
                    formData.append('id', target.dataset.id);
                }
                formData.append('csrf', getInlineCsrf());

                postForm(endpoints.upload, formData, function (error, data) {
                    if (error && !error.ok) {
                        var message = error.error ? error.error : 'Upload failed.';
                        showToast(message, 'error');
                        return;
                    }
                    var response = data || {};
                    if (response.ok === false) {
                        showToast(response.error ? response.error : 'Upload failed.', 'error');
                        return;
                    }
                    if (response.csrf) {
                        updateInlineCsrf(response.csrf);
                    }
                    var cacheBuster = response.cache_buster || ('?v=' + Date.now());
                    if (response.path) {
                        target.src = response.path + cacheBuster;
                    }
                    showToast('Image updated');
                });
            });
            input.click();
        }

        function openHtmlModal(el) {
            if (htmlModal) {
                return;
            }
            var modal = document.createElement('div');
            modal.className = 'admin-modal';

            var content = document.createElement('div');
            content.className = 'admin-modal__content';

            var textarea = document.createElement('textarea');
            textarea.value = (el.innerHTML || '').trim();
            content.appendChild(textarea);

            var actions = document.createElement('div');
            actions.className = 'admin-modal__actions';

            var cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'admin-modal__button cancel';
            cancelBtn.textContent = 'Cancel';
            cancelBtn.addEventListener('click', function () {
                closeHtmlModal();
            });

            var saveBtn = document.createElement('button');
            saveBtn.type = 'button';
            saveBtn.className = 'admin-modal__button save';
            saveBtn.textContent = 'Save';
            saveBtn.addEventListener('click', function () {
                var payload = buildPayload(el, textarea.value);
                postJson(endpoints.update, payload, function (error, data) {
                    if (error && !error.ok) {
                        var message = error.error ? error.error : 'Save failed.';
                        showToast(message, 'error');
                        return;
                    }
                    var response = data || {};
                    if (response.ok === false) {
                        showToast(response.error ? response.error : 'Save failed.', 'error');
                        return;
                    }
                    if (response.csrf) {
                        updateInlineCsrf(response.csrf);
                    }
                    el.innerHTML = response.value !== undefined ? response.value : textarea.value;
                    showToast('Saved');
                    closeHtmlModal();
                });
            });

            actions.appendChild(cancelBtn);
            actions.appendChild(saveBtn);
            content.appendChild(actions);
            modal.appendChild(content);
            document.body.appendChild(modal);
            htmlModal = modal;
        }

        function closeHtmlModal() {
            if (!htmlModal) {
                return;
            }
            htmlModal.parentNode.removeChild(htmlModal);
            htmlModal = null;
        }

        function openLogoutModal(form) {
            if (logoutModal) {
                return;
            }
            var modal = document.createElement('div');
            modal.className = 'admin-modal';

            var content = document.createElement('div');
            content.className = 'admin-modal__content';

            var message = document.createElement('p');
            message.className = 'admin-modal__message';
            message.textContent = 'Save changes and return to user mode?';
            content.appendChild(message);

            var actions = document.createElement('div');
            actions.className = 'admin-modal__actions';

            var stayButton = document.createElement('button');
            stayButton.type = 'button';
            stayButton.className = 'admin-modal__button cancel';
            stayButton.textContent = 'Stay';
            stayButton.addEventListener('click', function () {
                closeLogoutModal();
            });

            var confirmButton = document.createElement('button');
            confirmButton.type = 'button';
            confirmButton.className = 'admin-modal__button save';
            confirmButton.textContent = 'Save & Logout';
            confirmButton.addEventListener('click', function () {
                confirmButton.disabled = true;
                var payload = {
                    enabled: false,
                    csrf: getInlineCsrf()
                };
                postJson(endpoints.toggle, payload, function (error, data) {
                    if (error && !error.ok) {
                        confirmButton.disabled = false;
                        var message = error.error ? error.error : 'Logout failed.';
                        showToast(message, 'error');
                        return;
                    }
                    var response = data || {};
                    if (response.ok === false) {
                        confirmButton.disabled = false;
                        showToast(response.error ? response.error : 'Logout failed.', 'error');
                        return;
                    }
                    if (response.csrf) {
                        updateInlineCsrf(response.csrf);
                    }
                    inlineState.enabled = Boolean(response.enabled);
                    deactivateAdminMode();
                    closeLogoutModal();
                    form.submit();
                });
            });

            actions.appendChild(stayButton);
            actions.appendChild(confirmButton);
            content.appendChild(actions);
            modal.appendChild(content);
            document.body.appendChild(modal);
            logoutModal = modal;
        }

        function closeLogoutModal() {
            if (!logoutModal) {
                return;
            }
            logoutModal.parentNode.removeChild(logoutModal);
            logoutModal = null;
        }

        function startEditing(el) {
            var type = el.dataset.fieldType;
            if (type === 'html') {
                openHtmlModal(el);
                return;
            }
            if (type === 'url') {
                var current = el.getAttribute('href') || '';
                var next = window.prompt('New URL', current) || '';
                if (!next || next === current) {
                    return;
                }
                var payload = buildPayload(el, next);
                postJson(endpoints.update, payload, function (error, data) {
                    if (error && !error.ok) {
                        var message = error.error ? error.error : 'Save failed.';
                        showToast(message, 'error');
                        return;
                    }
                    var response = data || {};
                    if (response.ok === false) {
                        showToast(response.error ? response.error : 'Save failed.', 'error');
                        return;
                    }
                    if (response.csrf) {
                        updateInlineCsrf(response.csrf);
                    }
                    if (response.value) {
                        el.setAttribute('href', response.value);
                    } else {
                        el.setAttribute('href', next);
                    }
                    showToast('Saved');
                });
                return;
            }
            el.dataset.adminOriginal = el.textContent || '';
            el.dataset.adminEditing = 'true';
            el.setAttribute('contenteditable', 'true');
            el.classList.add('admin-editing-outline');
            el.focus();
            toggleButtons(el, { edit: false, save: true, cancel: true });
        }

        function cancelEditing(el) {
            if (el.dataset.fieldType === 'html') {
                closeHtmlModal();
                return;
            }
            if (el.dataset.adminOriginal !== undefined) {
                el.textContent = el.dataset.adminOriginal;
            }
            el.dataset.adminEditing = 'false';
            el.removeAttribute('contenteditable');
            el.classList.remove('admin-editing-outline');
            toggleButtons(el, { edit: true, save: false, cancel: false });
        }

        function saveEditing(el) {
            var value = el.dataset.fieldType === 'html' ? el.innerHTML : (el.textContent || '');
            var payload = buildPayload(el, value);
            postJson(endpoints.update, payload, function (error, data) {
                if (error && !error.ok) {
                    var message = error.error ? error.error : 'Save failed.';
                    showToast(message, 'error');
                    return;
                }
                var response = data || {};
                if (response.ok === false) {
                    showToast(response.error ? response.error : 'Save failed.', 'error');
                    return;
                }
                if (response.csrf) {
                    updateInlineCsrf(response.csrf);
                }
                if (el.dataset.fieldType !== 'html') {
                    el.textContent = response.value !== undefined ? response.value : value;
                }
                el.dataset.adminEditing = 'false';
                el.removeAttribute('contenteditable');
                el.classList.remove('admin-editing-outline');
                toggleButtons(el, { edit: true, save: false, cancel: false });
                showToast('Saved');
            });
        }

        function setupEditableElements() {
            qsa('[data-model][data-key]').forEach(function (el) {
                if (el.dataset.adminSetup === 'true') {
                    return;
                }
                el.dataset.adminSetup = 'true';
                var type = inferType(el);
                el.dataset.fieldType = type;
                var parent = el.parentNode;
                var wrapper = document.createElement('div');
                wrapper.className = 'admin-edit-overlay';
                if (parent) {
                    parent.insertBefore(wrapper, el);
                }
                wrapper.appendChild(el);

                var controls = document.createElement('div');
                controls.className = 'admin-action-buttons admin-hidden';
                controls.setAttribute('data-admin-controls', 'true');

                if (type === 'image') {
                    var replaceBtn = createActionButton('Replace', function () {
                        triggerImageReplace(el, wrapper);
                    });
                    controls.appendChild(replaceBtn);
                } else if (type === 'url') {
                    var editUrlBtn = createActionButton('Edit URL', function () {
                        startEditing(el);
                    });
                    controls.appendChild(editUrlBtn);
                } else {
                    var editBtn = createActionButton('Edit', function () {
                        startEditing(el);
                    });
                    var saveBtn = createActionButton('Save', function () {
                        saveEditing(el);
                    });
                    var cancelBtn = createActionButton('Cancel', function () {
                        cancelEditing(el);
                    });
                    saveBtn.classList.add('admin-hidden');
                    cancelBtn.classList.add('admin-hidden');
                    controls.appendChild(editBtn);
                    controls.appendChild(saveBtn);
                    controls.appendChild(cancelBtn);
                }

                wrapper.appendChild(controls);
            });
        }

        function activateAdminMode() {
            bodyElement.classList.add('admin-mode-active');
            if (!inlineState.initialized) {
                setupEditableElements();
                inlineState.initialized = true;
            }
            toggleInlineControls(true);
            updateToolbarStatus(true);
        }

        function deactivateAdminMode() {
            bodyElement.classList.remove('admin-mode-active');
            toggleInlineControls(false);
            cancelAllEdits();
            updateToolbarStatus(false);
        }

        function onToggleClick(event) {
            event.preventDefault();
            var desired = !inlineState.enabled;
            var payload = {
                enabled: desired,
                csrf: getInlineCsrf()
            };
            postJson(endpoints.toggle, payload, function (error, data) {
                if (error && !error.ok) {
                    var message = error.error ? error.error : 'Toggle failed.';
                    showToast(message, 'error');
                    return;
                }
                var response = data || {};
                if (response.ok === false) {
                    showToast(response.error ? response.error : 'Toggle failed.', 'error');
                    return;
                }
                if (response.csrf) {
                    updateInlineCsrf(response.csrf);
                }
                inlineState.enabled = Boolean(response.enabled);
                if (inlineState.enabled) {
                    activateAdminMode();
                } else {
                    deactivateAdminMode();
                }
            });
        }

        function onLogoutSubmit(event) {
            event.preventDefault();
            var form = event.currentTarget;
            if (!form || form.tagName !== 'FORM') {
                return;
            }
            openLogoutModal(form);
        }

        function handleCopyClick(event) {
            var trigger = event.target ? event.target.closest('[data-copy-url]') : null;
            if (!trigger) {
                return;
            }
            event.preventDefault();
            var value = trigger.getAttribute('data-copy-url') || '';
            copyToClipboard(value, function () {
                showToast('Copied to clipboard.');
            }, function () {
                showToast('Unable to copy URL.', 'error');
            });
        }

        function initToolbar() {
            var toggle = qs('[data-admin-toggle]');
            if (toggle) {
                toggle.addEventListener('click', onToggleClick);
            }
            var logoutForm = qs('.admin-toolbar__form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', onLogoutSubmit);
            }
            if (inlineState.enabled) {
                activateAdminMode();
            } else {
                deactivateAdminMode();
            }
            document.addEventListener('click', handleCopyClick);
        }

        initToolbar();

        window.addEventListener('beforeunload', function () {
            if (inlineState.enabled) {
                cancelAllEdits();
            }
        });
    }

    if (window.ADMIN_CONTEXT) {
        initInlineEditing(window.ADMIN_CONTEXT);
    }

    console.log('Admin dashboard script initialised');
});
