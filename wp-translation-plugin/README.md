# WP Translation Plugin

## Overview

The WP Translation Plugin allows WordPress users to input English strings and automatically translates them into multiple languages using the Weglot API. Translations are stored in a WordPress database and can be accessed via a REST API endpoint.

## Features

- **User Interface**: Provides a WordPress admin page to enter English strings.
- **Translation**: Translates entered strings into Brazilian Portuguese, Dutch, French, German, Italian, Japanese, Polish, Portuguese, Spanish, and Russian using the Weglot API.
- **Storage**: Stores translations in the WordPress database and updates them if they already exist.
- **REST API**: Exposes translated strings via a REST API endpoint.

## Installation

1. **Download and Install the Plugin**:

   - Compress the file into a ZIP archive named `wp-translation-plugin.zip`.
   - Go to your WordPress admin dashboard.
   - Navigate to **Plugins > Add New**.
   - Click **Upload Plugin**, choose the ZIP file, and click **Install Now**.
   - Activate the plugin from the Plugins page.

2. **Setup**:
   - Replace `YOUR_API_KEY` in the `wp-translation-plugin.php` file with your actual Weglot API key.

## Usage

1. **Access the Plugin Interface**:

   - Go to the WordPress admin dashboard.
   - Navigate to **Translation Strings** in the sidebar.

2. **Enter Strings for Translation**:

   - On the **Translation Strings** page, enter the English string you want to translate in the textarea.
   - Click **Save Translation**.

3. **View Translations**:

   - The plugin will automatically translate the string into the supported languages and save them in the database.

4. **Access Translations via REST API**:

   - You can access translations via the REST API endpoint. Use the following URL format to request translations for a specific language:

     http://<your-wordpress-site>/wp-json/wp-translation-plugin/v2/translations?language=<lang-code>

   - Replace `<your-wordpress-site>` with your WordPress site URL and `<lang-code>` with the desired language code (e.g., `fr` for French).

   **Example URL for French:**

   http://<your-wordpress-site>/wp-json/wp-translation-plugin/v2/translations?language=fr
