<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Unlimited Google Veo-3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/simple-style.css">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23 7l-7 5 7 5V7z" stroke="currentColor" stroke-width="2"/>
                    <rect x="1" y="5" width="15" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                </svg>
                <h1>Veo-3 Video Generator</h1>
            </div>
            <span class="status-badge">🟢 Free Unlimited</span>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-wrapper">
                <div class="intro-text">
                    <h2>Free Unlimited Google Veo-3</h2>
                    <p>Generate stunning AI videos from text prompts using Google's latest Veo-3 model.</p>
                </div>

                <!-- Generator Form -->
                <div class="generator-box">
                    <form id="videoForm">
                    <!-- API Credentials (Required) -->
                    <div class="form-group">
                        <label class="label">
                            🔑 Google Cloud Credentials (Required)
                        </label>
                        <div class="credentials-info">
                            <p>Pilih salah satu cara untuk authenticate:</p>
                        </div>
                    </div>

                    <!-- Option 1: Project ID + Access Token -->
                    <div class="form-group">
                        <details class="details-box" open>
                            <summary>Option 1: Access Token (Recommended)</summary>
                            <div class="details-content">
                                <div class="form-row">
                                    <div class="form-col">
                                        <label class="label-small">Project ID</label>
                                        <input 
                                            type="text" 
                                            id="projectId" 
                                            class="text-input" 
                                            placeholder="your-project-id"
                                            required
                                        >
                                    </div>
                                    <div class="form-col">
                                        <label class="label-small">Access Token</label>
                                        <input 
                                            type="password" 
                                            id="accessToken" 
                                            class="text-input" 
                                            placeholder="Get from: gcloud auth print-access-token"
                                        >
                                    </div>
                                </div>
                                <p class="hint-text">
                                    Get token: <code>gcloud auth print-access-token</code><br>
                                    Token expires in 1 hour, refresh if needed.
                                </p>
                            </div>
                        </details>
                    </div>

                    <!-- Option 2: Service Account JSON -->
                    <div class="form-group">
                        <details class="details-box">
                            <summary>Option 2: Service Account JSON</summary>
                            <div class="details-content">
                                <div class="form-row">
                                    <div class="form-col">
                                        <label class="label-small">Project ID</label>
                                        <input 
                                            type="text" 
                                            id="projectIdJson" 
                                            class="text-input" 
                                            placeholder="your-project-id"
                                        >
                                    </div>
                                </div>
                                <label class="label-small">Service Account JSON</label>
                                <textarea 
                                    id="serviceAccountJson" 
                                    class="textarea-input" 
                                    rows="4"
                                    placeholder='Paste your service account JSON here...'
                                ></textarea>
                                <p class="hint-text">
                                    Get from: Google Cloud Console → IAM & Admin → Service Accounts
                                </p>
                            </div>
                        </details>
                    </div>

                    <!-- Model Selection -->
                    <div class="form-group">
                        <label class="label">Pilih Model</label>
                        <select id="modelSelect" class="select-input">
                            <optgroup label="Veo 3.1 (Latest - Preview)">
                                <option value="veo-3.1-generate-preview">Veo 3.1 Generate (Preview) - Latest, Best Quality</option>
                                <option value="veo-3.1-fast-generate-preview">Veo 3.1 Fast (Preview) - Faster Generation</option>
                            </optgroup>
                            <optgroup label="Veo 3.0 (Stable)">
                                <option value="veo-3.0-generate-001" selected>Veo 3.0 Generate - Stable, High Quality</option>
                                <option value="veo-3.0-fast-generate-001">Veo 3.0 Fast - Quick Results</option>
                            </optgroup>
                            <optgroup label="Veo 2.0 (Previous Generation)">
                                <option value="veo-2.0-generate-001">Veo 2.0 Generate - 5-8 seconds</option>
                                <option value="veo-2.0-generate-exp">Veo 2.0 Experimental - Reference Images</option>
                                <option value="veo-2.0-generate-preview">Veo 2.0 Preview - Video Editing</option>
                            </optgroup>
                        </select>
                        <p class="model-info" id="modelInfo">High quality video generation with audio support</p>
                    </div>

                    <!-- Generation Type -->
                    <div class="form-group">
                        <label class="label">Generation Type</label>
                        <select id="generationType" class="select-input">
                            <option value="text-to-video">Text-to-Video</option>
                            <option value="image-to-video">Image-to-Video</option>
                            <option value="video-extend" disabled>Video Extension (Coming Soon)</option>
                        </select>
                    </div>                        <!-- Text-to-Video Mode -->
                        <div id="textMode" class="mode-content">
                            <div class="form-group">
                                <label class="label">Prompt</label>
                                <textarea 
                                    id="prompt" 
                                    class="textarea-input" 
                                    rows="4"
                                    placeholder="Contoh: A cat playing with a ball in a room"
                                    required
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label class="label">Negative Prompt (Optional)</label>
                                <textarea 
                                    id="negativePrompt" 
                                    class="textarea-input" 
                                    rows="2"
                                    placeholder="Contoh: no text overlays, no UI elements, no brand logos"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Image-to-Video Mode -->
                        <div id="imageMode" class="mode-content" style="display: none;">
                            <div class="form-group">
                                <label class="label">Upload Image</label>
                                <div class="upload-box" id="uploadBox">
                                    <input type="file" id="imageFile" accept="image/*" hidden>
                                    <div class="upload-placeholder" id="uploadPlaceholder">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        <p>Click to upload or drag and drop</p>
                                    </div>
                                    <div class="upload-preview" id="uploadPreview"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="label">Prompt (Optional)</label>
                                <textarea 
                                    id="imagePrompt" 
                                    class="textarea-input" 
                                    rows="2"
                                    placeholder="Describe how you want the image to be animated..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- Settings Toggle -->
                        <div class="form-group">
                            <button type="button" class="accordion-btn" id="settingsBtn">
                                <span>⚙️ Advanced Settings</span>
                                <span class="arrow">▼</span>
                            </button>
                            <div class="accordion-content" id="settingsPanel">
                                <div class="settings-grid">
                                    <div>
                                        <label class="label-small">Aspect Ratio</label>
                                        <select id="aspectRatio" class="select-input-sm">
                                            <option value="1:1">Square (1:1)</option>
                                            <option value="16:9">Landscape (16:9)</option>
                                            <option value="9:16">Portrait (9:16)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-small">Duration</label>
                                        <select id="duration" class="select-input-sm">
                                            <option value="4">4 seconds</option>
                                            <option value="5">5 seconds (Veo 2)</option>
                                            <option value="6">6 seconds</option>
                                            <option value="8" selected>8 seconds</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-small">Resolution</label>
                                        <select id="resolution" class="select-input-sm">
                                            <option value="720p">720p HD</option>
                                            <option value="1080p" selected>1080p Full HD</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-small">Samples</label>
                                        <select id="samples" class="select-input-sm">
                                            <option value="1" selected>1 video</option>
                                            <option value="2">2 videos</option>
                                            <option value="3">3 videos</option>
                                            <option value="4">4 videos</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-small">Compression</label>
                                        <select id="compression" class="select-input-sm">
                                            <option value="optimized" selected>Optimized</option>
                                            <option value="lossless">Lossless</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="checkbox-wrapper">
                                    <label class="checkbox-container">
                                        <input type="checkbox" id="enhancePrompt" checked>
                                        <span class="checkmark"></span>
                                        Enhance Prompt with AI (Gemini)
                                    </label>
                                    <label class="checkbox-container">
                                        <input type="checkbox" id="generateAudio" checked>
                                        <span class="checkmark"></span>
                                        Generate Audio (Veo 3+ only)
                                    </label>
                                    <label class="checkbox-container">
                                        <input type="checkbox" id="enableAdult">
                                        <span class="checkmark"></span>
                                        Allow Adult Content
                                    </label>
                                </div>
                            </div>
                        </div>



                        <!-- Generate Button -->
                        <button type="submit" class="btn-generate" id="generateBtn">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23 7l-7 5 7 5V7z" stroke="currentColor" stroke-width="2"/>
                                <rect x="1" y="5" width="15" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            Generate Video
                        </button>
                    </form>

                    <!-- Progress -->
                    <div class="progress-box" id="progressBox" style="display: none;">
                        <div class="progress-header">
                            <span class="progress-label">Generating...</span>
                            <span class="progress-percent" id="progressPercent">0%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <p class="progress-message" id="progressMessage">Initializing...</p>
                    </div>

                    <!-- Results -->
                    <div class="results-box" id="resultsBox" style="display: none;">
                        <div class="results-header">
                            <h3>Generated Videos</h3>
                            <button class="btn-secondary" onclick="resetForm()">New Video</button>
                        </div>
                        <div id="videoResults"></div>
                    </div>
                </div>

                <!-- Gallery -->
                <div class="gallery-box">
                    <h3>Recent Generations</h3>
                    <div class="gallery-grid" id="galleryGrid">
                        <p class="empty-state">No videos yet. Generate your first one!</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="app-footer">
            <p>Powered by Google Veo-3 • Bring Your Own Credentials</p>
        </footer>
    </div>

    <script src="assets/js/simple-app.js"></script>
</body>
</html>
