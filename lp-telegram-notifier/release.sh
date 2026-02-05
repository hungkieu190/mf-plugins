#!/bin/bash

# LP Telegram Notifier Release Script
# Creates a production-ready zip file for distribution

set -e

PLUGIN_NAME="lp-telegram-notifier"
VERSION=$(grep "Version:" lp-telegram-notifier.php | awk '{print $3}')
BUILD_DIR="build"
RELEASE_DIR="release"
ZIP_NAME="${PLUGIN_NAME}-${VERSION}.zip"

echo "🚀 Building LP Telegram Notifier v${VERSION}"

# Clean previous builds
echo "🧹 Cleaning previous builds..."
rm -rf ${BUILD_DIR}
rm -rf ${RELEASE_DIR}
mkdir -p ${BUILD_DIR}/${PLUGIN_NAME}
mkdir -p ${RELEASE_DIR}

echo "📦 Copying plugin files..."

# Copy main plugin file
cp lp-telegram-notifier.php ${BUILD_DIR}/${PLUGIN_NAME}/

# Copy README
cp README.txt ${BUILD_DIR}/${PLUGIN_NAME}/

# Copy includes directory
cp -r includes ${BUILD_DIR}/${PLUGIN_NAME}/

# Create languages directory (for i18n)
mkdir -p ${BUILD_DIR}/${PLUGIN_NAME}/languages

echo "🔍 Removing development files..."

# Remove any development files
find ${BUILD_DIR}/${PLUGIN_NAME} -name ".DS_Store" -delete
find ${BUILD_DIR}/${PLUGIN_NAME} -name "*.log" -delete
find ${BUILD_DIR}/${PLUGIN_NAME} -name "spec.txt" -delete
find ${BUILD_DIR}/${PLUGIN_NAME} -name "*.tmp" -delete

echo "📝 Creating changelog..."

# Extract changelog from README
grep -A 20 "== Changelog ==" README.txt > ${BUILD_DIR}/${PLUGIN_NAME}/CHANGELOG.txt || true

echo "🗜️  Creating zip file..."

# Create zip
cd ${BUILD_DIR}
zip -r ../${RELEASE_DIR}/${ZIP_NAME} ${PLUGIN_NAME} -q
cd ..

echo "✅ Release created successfully!"
echo ""
echo "📦 Package: ${RELEASE_DIR}/${ZIP_NAME}"
echo "📊 Size: $(du -h ${RELEASE_DIR}/${ZIP_NAME} | cut -f1)"
echo ""
echo "📋 Contents:"
unzip -l ${RELEASE_DIR}/${ZIP_NAME} | tail -n +4 | head -n -2

echo ""
echo "🎉 Ready to upload to mamflow.com!"
