#!/usr/bin/env bash
# Local HTTPS smoke test via the project-local CA and --resolve (no hosts/trust changes).
set -uo pipefail
cd "$(dirname "$0")/../../.." || exit 1
set -a; . infra/local/.env; set +a
H="${PROCUREMENT_HOSTNAME}"; P="${PROCUREMENT_HTTPS_PORT:-443}"; O="https://${H}$([ "$P" = 443 ] || echo ":$P")"
c() { curl -sS --cacert infra/local/ca/rootCA.pem --resolve "${H}:${P}:127.0.0.1" "$@"; }
tmp="$(mktemp)"
echo "## TLS verified with project CA (no -k)"; c -o /dev/null -w "%{http_code} ssl_verify_result=%{ssl_verify_result}\n" "$O/up"
echo "## HTTP -> HTTPS"; curl -sS --resolve "${H}:${PROCUREMENT_HTTP_PORT:-80}:127.0.0.1" -o /dev/null -w "%{http_code} -> %{redirect_url}\n" "http://${H}:${PROCUREMENT_HTTP_PORT:-80}/"
echo "## health"; c "$O/api/procurement/v1/health"; echo
echo "## unknown API routes are JSON 404"; for u in /api/procurement/v1/nope /api/other; do c -o /dev/null -w "$u %{http_code} %{content_type}\n" "$O$u"; done
echo "## /auth/me without session"; c "$O/api/procurement/v1/auth/me"; echo
echo "## SPA shell at /"; c "$O/" -o "$tmp" -w "%{http_code}\n"; grep -o '<div id="root"></div>\|/@vite/client\|/spa/assets/index-[A-Za-z0-9_-]*\.js' "$tmp" | sort -u
echo "## Statamic CMS page"; c -o "$tmp" -w "%{http_code}\n" "$O/pages/privacy"; grep -o 'Placeholder content — pending approval\|src/styles/cms-page.css\|/spa/assets/cms-[A-Za-z0-9_-]*\.css' "$tmp" | sort -u
echo "## CP login"; c -o /dev/null -w "%{http_code}\n" "$O/cp/auth/login"
echo "## security headers on /"; c -sI "$O/" | grep -i 'strict-transport\|x-content-type\|x-frame' | tr -d '\r'
echo "## request forgery: POST without XSRF token (with a live session cookie)"
jar="$(mktemp)"; c -c "$jar" -o /dev/null "$O/sanctum/csrf-cookie"
for site in cross-site same-site none same-origin; do
  hdr=(); [ "$site" = none ] || hdr=(-H "Sec-Fetch-Site: $site")
  c -b "$jar" ${hdr[@]+"${hdr[@]}"} -H 'Accept: application/json' -X POST -o /dev/null -w "Sec-Fetch-Site=$site -> %{http_code}\n" "$O/api/procurement/v1/auth/forgot-password" --data 'email=nobody@example.test'
done
rm -f "$jar"
echo "## MySQL not published on host"; (nc -z -w2 127.0.0.1 3306 && echo "3306 OPEN (unexpected)") || echo "3306 closed"
rm -f "$tmp"
