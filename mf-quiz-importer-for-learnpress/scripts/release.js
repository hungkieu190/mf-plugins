const fs = require('fs');
const path = require('path');
const archiver = require('archiver');
const { execSync } = require('child_process');

// Configuration
const PLUGIN_SLUG = 'mf-quiz-importer-for-learnpress';
const RELEASE_DIR = 'release';
const BUILD_DIR = path.join(RELEASE_DIR, PLUGIN_SLUG);

// Get version from package.json
const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const VERSION = packageJson.version;

console.log('🚀 Building Quiz Importer for LearnPress v' + VERSION);
console.log('================================================\n');

// Files and directories to include
const INCLUDE_PATTERNS = [
    // Main plugin file
    'mf-quiz-importer-for-learnpress.php',
    
    // Core directories
    'includes/**/*.php',
    'assets/**/*.css',
    'assets/**/*.js',
    'assets/**/*.min.css',
    'assets/**/*.min.js',
    'languages/**/*',
    'samples/**/*',
    
    // Documentation
    'README.md',
    'CHANGELOG.md',
    'QUICK-START.md',
    'IMPORT-GUIDE.md',
    'QUESTION-TYPES.md',
    'FEATURES.md',
    'LEARNPRESS-INTEGRATION.md',
    'UI-GUIDE.md',
    'DEBUG-GUIDE.md',
    'TEST-GUIDE.md',
    'PLUGIN-STRUCTURE.md',
    'DOCUMENTATION-INDEX.md',
    'RELEASE-NOTES.md',
    
    // License
    'LICENSE',
    'license.txt'
];

// Files and directories to exclude
const EXCLUDE_PATTERNS = [
    'node_modules',
    'source_lp',
    'scripts',
    'release',
    '.git',
    '.github',
    '.gitignore',
    '.gitattributes',
    '.editorconfig',
    '.eslintrc',
    '.prettierrc',
    'package.json',
    'package-lock.json',
    'composer.json',
    'composer.lock',
    'phpcs.xml',
    'phpunit.xml',
    'webpack.config.js',
    'gulpfile.js',
    'Gruntfile.js',
    '.DS_Store',
    'Thumbs.db',
    '*.log',
    '*.tmp',
    '*.bak',
    '*~',
    '*.swp'
];

// Clean and create directories
function setupDirectories() {
    console.log('📁 Setting up directories...');
    
    // Remove old release directory
    if (fs.existsSync(RELEASE_DIR)) {
        fs.rmSync(RELEASE_DIR, { recursive: true, force: true });
    }
    
    // Create new directories
    fs.mkdirSync(RELEASE_DIR, { recursive: true });
    fs.mkdirSync(BUILD_DIR, { recursive: true });
    
    console.log('✅ Directories ready\n');
}

// Check if path should be excluded
function shouldExclude(filePath) {
    return EXCLUDE_PATTERNS.some(pattern => {
        if (pattern.includes('*')) {
            const regex = new RegExp(pattern.replace(/\*/g, '.*'));
            return regex.test(filePath);
        }
        return filePath.includes(pattern);
    });
}

// Copy files to build directory
function copyFiles() {
    console.log('📋 Copying plugin files...');
    
    const copyRecursive = (src, dest) => {
        const stats = fs.statSync(src);
        
        if (stats.isDirectory()) {
            if (!fs.existsSync(dest)) {
                fs.mkdirSync(dest, { recursive: true });
            }
            
            const files = fs.readdirSync(src);
            files.forEach(file => {
                const srcPath = path.join(src, file);
                const destPath = path.join(dest, file);
                
                if (!shouldExclude(srcPath)) {
                    copyRecursive(srcPath, destPath);
                }
            });
        } else {
            fs.copyFileSync(src, dest);
            console.log('  ✓ ' + src);
        }
    };
    
    // Copy main plugin file
    if (fs.existsSync('mf-quiz-importer-for-learnpress.php')) {
        fs.copyFileSync(
            'mf-quiz-importer-for-learnpress.php',
            path.join(BUILD_DIR, 'mf-quiz-importer-for-learnpress.php')
        );
        console.log('  ✓ mf-quiz-importer-for-learnpress.php');
    }
    
    // Copy directories
    const directories = ['includes', 'assets', 'languages', 'samples'];
    directories.forEach(dir => {
        if (fs.existsSync(dir)) {
            copyRecursive(dir, path.join(BUILD_DIR, dir));
        }
    });
    
    // Copy documentation files
    const docs = [
        'README.md',
        'CHANGELOG.md',
        'QUICK-START.md',
        'IMPORT-GUIDE.md',
        'QUESTION-TYPES.md',
        'FEATURES.md',
        'LEARNPRESS-INTEGRATION.md',
        'UI-GUIDE.md',
        'DEBUG-GUIDE.md',
        'TEST-GUIDE.md',
        'PLUGIN-STRUCTURE.md',
        'DOCUMENTATION-INDEX.md',
        'RELEASE-NOTES.md'
    ];
    
    docs.forEach(doc => {
        if (fs.existsSync(doc)) {
            fs.copyFileSync(doc, path.join(BUILD_DIR, doc));
            console.log('  ✓ ' + doc);
        }
    });
    
    console.log('✅ Files copied\n');
}

// Create ZIP archive
function createZip() {
    console.log('📦 Creating ZIP archive...');
    
    return new Promise((resolve, reject) => {
        const zipPath = path.join(RELEASE_DIR, `${PLUGIN_SLUG}-${VERSION}.zip`);
        const output = fs.createWriteStream(zipPath);
        const archive = archiver('zip', { zlib: { level: 9 } });
        
        output.on('close', () => {
            const sizeMB = (archive.pointer() / 1024 / 1024).toFixed(2);
            console.log('✅ ZIP created: ' + zipPath);
            console.log('📊 Size: ' + sizeMB + ' MB\n');
            resolve();
        });
        
        archive.on('error', (err) => {
            reject(err);
        });
        
        archive.pipe(output);
        archive.directory(BUILD_DIR, PLUGIN_SLUG);
        archive.finalize();
    });
}

// Generate release info
function generateReleaseInfo() {
    console.log('📝 Generating release info...');
    
    const info = {
        name: 'Quiz Importer for LearnPress',
        version: VERSION,
        slug: PLUGIN_SLUG,
        buildDate: new Date().toISOString(),
        files: {
            zip: `${PLUGIN_SLUG}-${VERSION}.zip`,
            directory: PLUGIN_SLUG
        },
        requirements: {
            wordpress: '5.8+',
            php: '7.4+',
            learnpress: '4.0+'
        }
    };
    
    const infoPath = path.join(RELEASE_DIR, 'release-info.json');
    fs.writeFileSync(infoPath, JSON.stringify(info, null, 2));
    
    console.log('✅ Release info saved\n');
}

// Main build process
async function build() {
    try {
        console.log('Starting build process...\n');
        
        setupDirectories();
        copyFiles();
        await createZip();
        generateReleaseInfo();
        
        console.log('================================================');
        console.log('🎉 Build completed successfully!');
        console.log('================================================\n');
        console.log('📦 Release package: release/' + PLUGIN_SLUG + '-' + VERSION + '.zip');
        console.log('📁 Build directory: release/' + PLUGIN_SLUG);
        console.log('\n✨ Ready to distribute!\n');
        
    } catch (error) {
        console.error('❌ Build failed:', error);
        process.exit(1);
    }
}

// Run build
build();
