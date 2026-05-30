#!/bin/sh

set -eu

cd /var/www/html

LOCK_DIR="vendor/.composer-install.lock"
HASH_FILE="vendor/.composer.lock.sha1"

current_hash() {
    sha1sum composer.lock | awk '{print $1}'
}

installed_hash() {
    if [ -f "${HASH_FILE}" ]; then
        cat "${HASH_FILE}"
        return 0
    fi

    return 0
}

needs_install() {
    [ -f vendor/autoload.php ] || return 0
    [ -f vendor/composer/installed.php ] || return 0
    [ "$(installed_hash)" = "$(current_hash)" ] || return 0
    return 1
}

if ! needs_install; then
    exit 0
fi

mkdir -p vendor

while ! mkdir "${LOCK_DIR}" 2>/dev/null; do
    sleep 1
done

cleanup() {
    rmdir "${LOCK_DIR}" 2>/dev/null || true
}

trap cleanup EXIT INT TERM

if ! needs_install; then
    exit 0
fi

composer install --no-interaction --prefer-dist --no-progress

printf '%s\n' "$(current_hash)" > "${HASH_FILE}"
