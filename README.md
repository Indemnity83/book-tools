# 📚 Book Tools (`librarian` CLI)

A simple command-line tool for managing audiobook files and organizing them into a clean, structured library.  
Perfect for post-processing audiobooks prepared via AudioBookShelf or similar apps.

> **Command name:** `librarian`  
> **Folder / repo name:** `book-tools`  
> **Current available command:** `shelve`

---

## 🚀 Commands

### `shelve`

Organizes and moves books from an import directory into a structured destination library.

```bash
php librarian shelve {importFolder} {destinationFolder?} {--dry-run} {--pretend}
````

#### Arguments

| Argument            | Description                                                                                                        |
| ------------------- | ------------------------------------------------------------------------------------------------------------------ |
| `importFolder`      | (Required) Path to the import folder. This folder should contain subfolders for each book with metadata and files. |
| `destinationFolder` | (Optional) Path to the destination library root. Defaults to the current working directory if not provided.        |

#### Options

| Option      | Description                                          |
| ----------- | ---------------------------------------------------- |
| `--dry-run` | Shows what would be done without making any changes. |
| `--pretend` | Alias for `--dry-run`.                               |

---

## 📦 How it works

The `shelve` command will:

1. Scan the `importFolder` for book subfolders.
2. Each book folder **must contain a `metadata.json` file** (produced by AudioBookShelf).
3. Files will be moved into:

```
[Author]/
  [Series]/
    [Series #] - [Title]/
      Title, Book [#] of [Series] by [Author].m4b
      cover.jpg
      metadata.json
```

* If no series → simpler path without series folder.
* If multiple audio files → filenames will include `Part 1`, `Part 2`, etc.

4. After processing:

    * If in `--dry-run` → no files will be moved, only output shown.
    * If real mode → files are moved, extra files (cover, metadata) copied, and original import folder removed if empty.

---

## ✅ Example

```bash
php librarian shelve ~/audiobooks/_import ~/audiobooks
```

Will scan `_import` folder and move books to the main audiobooks library, cleaning up the import folder after.

```bash
php librarian shelve ~/audiobooks/_import ~/audiobooks --dry-run
```

Will show exactly what would happen, but not move anything.

---

## 📚 Future roadmap

This is version 1 (MVP) with only the `shelve` command.
Future tools planned may include:

* `scan` → check library for missing metadata
* `rename` → force renaming of existing library to match patterns
* `cleanup` → remove orphaned or duplicate files

---

## 🧹 Development and testing

Feature and unit tests are provided using PestPHP.
To run tests:

```bash
vendor/bin/pest
```

You can also test the CLI directly:

```bash
php librarian shelve --help
```

---

## 👷‍♂️ Contributors

Currently maintained by **indemnity83**.
Pull requests welcome as the tool expands!

---

## 📜 License

MIT — do what you want, but please don’t sell it without adding value.
