(function () {
    function applyPageState() {
        var pageName = (document.body && document.body.dataset.page) || (location.pathname.split('/').pop() || 'index.html').replace(/\.html$/i, '');
        document.querySelectorAll('.header__menu [data-page]').forEach(function (link) {
            var isCurrent = link.getAttribute('data-page') === pageName;
            link.classList.toggle('active', isCurrent);
        });
        var title = document.body && document.body.dataset.title ? document.body.dataset.title : 'SOHI Attire';
        document.title = title;
    }

    function insertScriptBlock(script, html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var headNodes = Array.from(doc.head.childNodes);
        var bodyNodes = Array.from(doc.body.childNodes);

        if (headNodes.length) {
            headNodes.forEach(function (node) {
                document.head.appendChild(node.cloneNode(true));
            });
        }

        if (bodyNodes.length) {
            bodyNodes.forEach(function (node) {
                if (node.nodeType === 1 && node.tagName === 'SCRIPT') {
                    var newScript = document.createElement('script');
                    Array.from(node.attributes).forEach(function (attr) {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    newScript.textContent = node.textContent;
                    document.body.appendChild(newScript);
                } else if (node.nodeType === 1 || node.nodeType === 3) {
                    document.body.appendChild(node.cloneNode(true));
                }
            });
        }

        if (script && script.parentNode) {
            script.parentNode.removeChild(script);
        }
    }

    function insertPartial(target, html, kind) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        if (kind === 'head') {
            var headFragment = document.createDocumentFragment();
            Array.from(doc.head.childNodes).forEach(function (node) {
                headFragment.appendChild(node.cloneNode(true));
            });
            document.head.appendChild(headFragment);
            if (target && target.parentNode) {
                target.parentNode.removeChild(target);
            }
            return;
        }

        if (target && target.tagName === 'SCRIPT') {
            insertScriptBlock(target, html);
            return;
        }

        var fragment = document.createDocumentFragment();
        Array.from(doc.body.childNodes).forEach(function (node) {
            if (node.nodeType === 1 && node.tagName === 'SCRIPT') {
                var script = document.createElement('script');
                Array.from(node.attributes).forEach(function (attr) {
                    script.setAttribute(attr.name, attr.value);
                });
                if (node.textContent) {
                    script.textContent = node.textContent;
                }
                fragment.appendChild(script);
            } else if (node.nodeType === 1 || node.nodeType === 3) {
                fragment.appendChild(node.cloneNode(true));
            }
        });

        if (target && target.parentNode) {
            target.parentNode.replaceChild(fragment, target);
        }
    }

    function loadPartial(target, file) {
        var targetKind = target && target.dataset && target.dataset.target ? target.dataset.target : 'body';
        return fetch(file, { cache: 'no-store' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to load ' + file + ': ' + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                insertPartial(target, html, targetKind);
            })
            .catch(function (error) {
                console.warn(error.message);
            });
    }

    function loadIncludes() {
        var includeTargets = document.querySelectorAll('[data-include]');
        var requests = Array.from(includeTargets).map(function (element) {
            var file = element.getAttribute('data-include');
            return loadPartial(element, file);
        });

        Promise.all(requests).then(function () {
            applyPageState();
            document.dispatchEvent(new CustomEvent('partials:loaded'));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadIncludes, { once: true });
    } else {
        loadIncludes();
    }
})();
