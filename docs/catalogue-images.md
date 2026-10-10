# Book covers and sample profile portraits

The seed data contains HTTPS image URLs for all 220 books and 106 users. Existing installations receive images only where `books.cover_image` or `users.profile_photo` is null, empty, or whitespace. Existing uploads and external URLs are preserved.

## Image sources

- 158 seed book records use real covers from [Open Library](https://openlibrary.org/dev/docs/api/covers), matched by title and author. The sample ISBNs in this catalogue were not used for lookups. A cover may depict a different edition, including records named `Edition 2`.
- The remaining 62 seed records use a general library photograph from [Unsplash](https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=600&q=80). These are placeholders because the title and author could not be matched reliably. Unrecognized books in an existing database use the same photograph.
- Users without photos receive deterministic [Random User](https://randomuser.me/documentation) sample portraits. These are stock placeholders, not verified photographs of the named accounts. Users can replace them through the existing profile upload feature.

`database/JSON/book-image-sources.json` records the source and image type for each distinct seed title. All selected image URLs returned HTTP 200 with an image content type when checked on October 10, 2026. The application displays remote images; their ongoing availability depends on the providers.

## Update an existing installation

Deploy the updated PHP files and `database/JSON` files together, then run:

```sh
php artisan migrate --force
php artisan optimize:clear
```

Migration `2026_10_10_000002_fill_missing_book_and_profile_images` applies the backfill automatically without network requests. It changes only the two image fields; circulation, fine, account, and inventory data are preserved. Rollback preserves the image values.

To preview or repeat the backfill later:

```sh
php artisan media:fill-missing-images --dry-run
php artisan media:fill-missing-images
```

Do not rerun the full database seeders on an existing installation for this update. Fresh installations use the image URLs directly from the normal book and user seed data. There is no need to download images, change storage links, or rebuild frontend assets.
