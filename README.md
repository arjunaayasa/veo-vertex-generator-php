
# Veo Video Generator

Modern PHP web app for generating videos with Google Cloud Vertex AI Veo models.

---

## Overview

Veo Video Generator delivers a modern style interface on top of Google Cloud Vertex AI. Users bring their own credentials, choose any available Veo model, and generate video directly from text or image prompts without editing config files.

## Feature Highlights

- Sleek, responsive UI inspired by modern React dashboards
- Text-to-video and image-to-video flows with prompt enhancement
- Full Veo model catalog (3.1 preview, 3.0 stable, 2.0 legacy)
- Audio toggle (for Veo 3.x) and NSFW filter controls
- Real-time status updates, gallery, and multi-sample generation

## Tech Stack

- **Backend:** PHP 8+, cURL, OpenSSL
- **Frontend:** Vanilla JavaScript, CSS animations, Inter typeface
- **Cloud:** Google Cloud Vertex AI Video API (Veo family)

## Requirements

- PHP 7.4+ with cURL & OpenSSL extensions enabled
- Google Cloud project with Vertex AI API enabled
- Either `gcloud` CLI (for access tokens) or a service account key with Vertex AI User role
- Composer (if you plan to install PHP dependencies locally)

## Quick Start

```bash
# 1. Clone
git clone <repository-url> veo3
cd veo3

# 2. (Optional) Install PHP dependencies
composer install

# 3. Start the dev server
php -S localhost:8000

# 4. Open in browser
open http://localhost:8000
```

## Bring Your Own Credentials

The app supports two zero-config authentication paths. Pick whichever fits your workflow:

### Option 1: Access Token (great for fast prototyping)

1. Run `gcloud auth login` (one time).
2. Generate a fresh token whenever needed:
   ```bash
   gcloud auth print-access-token
   ```
3. Paste the token plus your Project ID into the form.

Tokens expire in ~60 minutes—just rerun the command when needed.

### Option 2: Service Account JSON (ideal for teams & automation)

1. In Google Cloud Console, create a service account with the **Vertex AI User** role.
2. Download the JSON key.
3. Paste the JSON (plus Project ID) into the form.

The backend signs a JWT on the fly, so no file uploads or manual config edits are required.

## Using the App

1. Choose **Text** or **Image** generation.
2. Provide either an access token or service account JSON.
3. Enter your prompt (and upload an image if needed).
4. Pick model, duration, aspect ratio, resolution, and sample count.
5. Hit **Generate Video** and watch progress update live.
6. Download finished clips from the gallery panel.

## Supported Models

| Model ID | Generation | Notes |
| -------- | ---------- | ----- |
| `veo-3.1-generate-preview` | Text/Image | Latest quality, supports audio |
| `veo-3.1-fast-generate-preview` | Text/Image | Faster preview builds |
| `veo-3.0-generate-001` | Text/Image | Stable production release |
| `veo-3.0-fast-generate-001` | Text/Image | Lower latency, reduced quality |
| `veo-2.0-generate-001` | Text/Image | 5–8 second clips |
| `veo-2.0-generate-exp` | Text/Image | Experimental, strong image reference mode |
| `veo-2.0-generate-preview` | Edit/Image | Legacy preview model |

> Audio generation is available on Veo 3.x models only.

## Project Structure

```
veo3/
├── index.php                 # Main application entry
├── api/
│   ├── generate.php         # Handles generation requests & credentials
│   ├── poll.php             # Polls operation status
│   └── gallery.php          # Returns generated media list
├── assets/
│   ├── css/
│   │   └── simple-style.css # Modern dark theme styles
│   └── js/
│       └── simple-app.js    # Frontend logic & UI state
├── includes/
│   └── VertexAIClient.php   # Veo client with token/JWT helpers
├── data/
│   └── gallery.json         # Lightweight gallery storage
├── logs/
│   └── error.log            # Error logging
└── config/                  # Optional legacy config support
    └── config.php
```

## REST Endpoints

- `POST api/generate.php` – Launches a new generation job.
- `POST api/poll.php` – Checks the status of an operation by name.
- `GET api/gallery.php` – Returns gallery metadata and URLs.

Sample `POST api/generate.php` payload:

```json
{
  "mode": "text-to-video",
  "projectId": "your-project-id",
  "accessToken": "ya29.a0...",
  "model": "veo-3.1-generate-preview",
  "prompt": "A cinematic drone shot over neon rice fields at dusk",
  "duration": 6,
  "aspectRatio": "16:9",
  "resolution": "1080p",
  "sampleCount": 2,
  "enhancePrompt": true,
  "generateAudio": true
}
```

## Troubleshooting

- **`Project ID is required`** – Provide it in either credential block.
- **`Either Access Token or Service Account JSON is required`** – Supply at least one credential path.
- **`Failed to get access token from service account`** – Verify the JSON includes `client_email`, `private_key`, and `token_uri`.
- **`Permission denied`** – Ensure Vertex AI API is enabled and billing is active. The caller must have the Vertex AI User role.
- **Slow or stalled generations** – Preview models can queue during high demand. Try the fast variants or Veo 3.0 stable.

## Security & Cost Notes

- Credentials stay on the client unless you enable server-side logging—still, avoid sharing tokens beyond trusted environments.
- Service account users should rotate keys regularly and scope them to a dedicated project.
- Vertex AI video generation is billed per clip length; monitor usage in Cloud Console.

## License

MIT License. Build on it, remix it, ship it.

## Support & Resources

- Google Cloud Vertex AI docs: https://cloud.google.com/vertex-ai/docs
- Veo model overview: https://cloud.google.com/vertex-ai/generative-ai/docs/video/generate-videos
- Issues & questions: check `logs/error.log` or reach out via repository issues.

---

Crafted for creators who want Veo power with minimal setup.
