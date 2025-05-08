<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [0.0.2](https://github.com/Indemnity83/book-tools/compare/v0.0.1...v0.0.2) (2025-05-08)

### Added

* Support for self-updating the application via GitHub releases (use `librarian self-update`)

### Changed

* Configured Conventional Commits and automatic changelog generation.
* Added GitHub Actions workflows for continuous integration and release processes.
* Updated `actions/checkout` GitHub Action from v3 to v4.
* Updated `softprops/action-gh-release` GitHub Action from v1 to v2.

---

## [0.0.1](https://github.com/Indemnity83/book-tools/compare/75d2ec559fb29fb8a0395306df4610b58e8bde0e...v0.0.1) (2025-05-08)

The first official release of **Librarian**, a simple CLI tool for organizing and shelving audiobooks.

### 🚀 Features

* `shelve` command for moving audiobooks from an import folder into a structured library
* Support for audio and extra files (e.g. cover images, PDFs, metadata)
* Automatic filename formatting with series and part numbers
* Intelligent folder structure generation based on metadata
* Dry run mode (`--dry-run` / `--pretend`) for previewing actions safely
* Automatically deletes import folder when empty (unless in dry run)
* Optimized for use after tagging/organizing in AudioBookShelf or similar tools
* Available as a single-file PHAR for easy distribution

---

