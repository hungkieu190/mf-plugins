#!/bin/bash

# LP Lesson Completion Sound - Release Script
# Version: 1.0.0

echo "🚀 Starting release process for LP Lesson Completion Sound..."

# Get plugin directory
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_NAME="lp-lesson-completion-sound"
VERSION="1.0.0"
RELEASE_DIR="$PLUGIN_DIR/release"
ZIP_NAME="${PLUGIN_NAME}-v${VERSION}.zip"

echo "📁 Plugin directory: $PLUGIN_DIR"
echo "📦 Creating release: $ZIP_NAME"

# Create release directory if it doesn't exist
mkdir -p "$RELEASE_DIR"

# Change to parent directory
cd "$PLUGIN_DIR/.."

# Create ZIP file excluding unnecessary files
zip -r "$RELEASE_DIR/$ZIP_NAME" "$PLUGIN_NAME" \
    -x "*.git*" \
    -x "*node_modules/*" \
    -x "*.DS_Store" \
    -x "*/.idea/*" \
    -x "*/.vscode/*" \
    -x "*/release.sh" \
    -x "*/release/*" \
    -x "*/concept.md" \
    -x "*.log" \
    -x "*~" \
    -x "*.tmp"

# Check if ZIP was created successfully
if [ -f "$RELEASE_DIR/$ZIP_NAME" ]; then
    echo "✅ Release created successfully!"
    echo "📍 Location: $RELEASE_DIR/$ZIP_NAME"
    
    # Get file size
    SIZE=$(du -h "$RELEASE_DIR/$ZIP_NAME" | cut -f1)
    echo "📊 File size: $SIZE"
    
    # List contents
    echo ""
    echo "📋 Package contents:"
    unzip -l "$RELEASE_DIR/$ZIP_NAME" | head -20
    
    echo ""
    echo "🎉 Release ready for upload to Mamflow.com!"
    echo "📝 Product ID: 47218"
    echo "🔢 Version: $VERSION"
else
    echo "❌ Error: Failed to create release ZIP"
    exit 1
fi
