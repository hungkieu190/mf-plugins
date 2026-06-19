$ErrorActionPreference = 'Stop'

$pluginSlug = 'bulk-edit-learnpress-prices'
$root = Resolve-Path (Join-Path $PSScriptRoot '..')
$releaseDir = Join-Path $root 'release'
$stagingRoot = Join-Path $releaseDir '.staging'
$stagingPluginDir = Join-Path $stagingRoot $pluginSlug
$mainPluginFile = Join-Path $root "$pluginSlug.php"

if (-not (Test-Path -LiteralPath $mainPluginFile)) {
	throw "Main plugin file not found: $mainPluginFile"
}

$header = Get-Content -LiteralPath $mainPluginFile -Raw
$versionMatch = [regex]::Match($header, 'Version:\s*([0-9A-Za-z\.\-_]+)')

if (-not $versionMatch.Success) {
	throw 'Unable to determine plugin version from the plugin header.'
}

$version = $versionMatch.Groups[1].Value
$zipPath = Join-Path $releaseDir "$pluginSlug-$version.zip"

New-Item -ItemType Directory -Force -Path $releaseDir | Out-Null

$resolvedRoot = (Resolve-Path -LiteralPath $root).Path
$resolvedReleaseDir = (Resolve-Path -LiteralPath $releaseDir).Path

if (-not $resolvedReleaseDir.StartsWith($resolvedRoot, [System.StringComparison]::OrdinalIgnoreCase)) {
	throw "Refusing to clean release directory outside plugin root: $resolvedReleaseDir"
}

if (Test-Path -LiteralPath $stagingRoot) {
	$resolvedStagingRoot = (Resolve-Path -LiteralPath $stagingRoot).Path
	if (-not $resolvedStagingRoot.StartsWith($resolvedReleaseDir, [System.StringComparison]::OrdinalIgnoreCase)) {
		throw "Refusing to remove staging directory outside release directory: $resolvedStagingRoot"
	}
	Remove-Item -LiteralPath $stagingRoot -Recurse -Force
}

if (Test-Path -LiteralPath $zipPath) {
	Remove-Item -LiteralPath $zipPath -Force
}

New-Item -ItemType Directory -Force -Path $stagingPluginDir | Out-Null

$includeItems = @(
	"$pluginSlug.php",
	'readme.txt',
	'DEVELOPER-HOOKS.md',
	'assets',
	'includes',
	'languages',
	'templates'
)

foreach ($item in $includeItems) {
	$source = Join-Path $root $item
	if (-not (Test-Path -LiteralPath $source)) {
		throw "Release source item not found: $source"
	}

	Copy-Item -LiteralPath $source -Destination $stagingPluginDir -Recurse -Force
}

Compress-Archive -LiteralPath $stagingPluginDir -DestinationPath $zipPath -CompressionLevel Optimal -Force
Remove-Item -LiteralPath $stagingRoot -Recurse -Force

Write-Output "Created release package: $zipPath"
