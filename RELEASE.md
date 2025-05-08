# 📦 Releasing `librarian`

Follow these steps to build and publish a new release of the `librarian` CLI tool:

---

## ✅ 1. Create a GitHub Release

* Go to **GitHub → Releases → Draft a new release**
* Use a tag that matches the version, e.g. `v1.0.0`
* Use the release title and notes as you wish (changelog, etc)
* **Publish the release**

> 💡 Tip: The tag name (e.g. `v1.0.0`) will automatically be passed as the `--build-version` during PHAR build.

---

## ✅ 2. Automated Build

Once the release is published:

* GitHub Actions will automatically start the `Build` workflow.
* The CLI will be built using:

```bash
php librarian app:build --build-version=<tag>
```

* The built PHAR will be stored at:

```
builds/librarian
```

---

## ✅ 3. Upload to Release (automatic)

The workflow will automatically upload `librarian` to the GitHub Release you created.

You should see:

```
Downloads
librarian
Source code (.zip)
Source code (.tar.gz)
```

---

## ✅ 4. Test the release (optional but recommended)

Download the `librarian` file from the release page and test:

```bash
php librarian shelve --help
```

✅ This verifies that it runs properly before sharing broadly.

---

## 📢 Notes

* `GITHUB_TOKEN` is automatically provided and used for uploads.
* No manual PHAR building is necessary — release publishing → triggers everything.
* This project uses Laravel Zero's `app:build` command for PHAR compilation (no Box setup needed).

---

## 🚦 Bonus Tip (optional future improvement)

If desired → add "latest" release tag and URL (e.g. via `latest` release or GitHub Pages) to make download links easy for users:

```
https://github.com/your-org/book-tools/releases/latest/download/librarian
```

✅ So users can always download "the latest" easily.

---

# ✅ Summary

> **Tag → Release → GitHub Action → PHAR → Release Assets → Done ✅**
