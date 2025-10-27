// Simple App for Veo-3 Video Generator
const API_ENDPOINT = 'api/generate.php';
const POLL_ENDPOINT = 'api/poll.php';
const GALLERY_ENDPOINT = 'api/gallery.php';

let currentMode = 'text-to-video';
let currentModel = 'veo-3.0-generate-001';
let currentOperation = null;
let pollingInterval = null;

// Model information
const MODEL_INFO = {
    'veo-3.1-generate-preview': {
        name: 'Veo 3.1 Generate (Preview)',
        description: 'Latest model with best quality, supports reference images and video extension',
        duration: [4, 6, 8],
        audio: true,
        features: ['Reference Images', 'Video Extension', 'Best Quality']
    },
    'veo-3.1-fast-generate-preview': {
        name: 'Veo 3.1 Fast (Preview)',
        description: 'Faster generation with great quality',
        duration: [4, 6, 8],
        audio: true,
        features: ['Fast Generation', 'Good Quality']
    },
    'veo-3.0-generate-001': {
        name: 'Veo 3.0 Generate',
        description: 'Stable model with high quality and audio support',
        duration: [4, 6, 8],
        audio: true,
        features: ['Stable', 'High Quality', 'Audio Support']
    },
    'veo-3.0-fast-generate-001': {
        name: 'Veo 3.0 Fast',
        description: 'Quick results with good quality',
        duration: [4, 6, 8],
        audio: true,
        features: ['Fast', 'Good Quality']
    },
    'veo-2.0-generate-001': {
        name: 'Veo 2.0 Generate',
        description: 'Previous generation, 5-8 seconds duration',
        duration: [5, 6, 7, 8],
        audio: false,
        features: ['Stable', '5-8 seconds']
    },
    'veo-2.0-generate-exp': {
        name: 'Veo 2.0 Experimental',
        description: 'Supports reference images for guided generation',
        duration: [5, 6, 7, 8],
        audio: false,
        features: ['Reference Images', 'Asset/Style Images']
    },
    'veo-2.0-generate-preview': {
        name: 'Veo 2.0 Preview',
        description: 'Video editing features (masking, extension)',
        duration: [5, 6, 7, 8],
        audio: false,
        features: ['Video Masking', 'Video Extension']
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', init);

function init() {
    setupModelInfo();
    setupModeSwitch();
    setupFileUpload();
    setupAccordion();
    setupForm();
    loadGallery();
}

// Model Info Display
function setupModelInfo() {
    const modelSelect = document.getElementById('modelSelect');
    const modelInfo = document.getElementById('modelInfo');
    
    modelSelect.addEventListener('change', (e) => {
        currentModel = e.target.value;
        const info = MODEL_INFO[currentModel];
        
        if (info) {
            const features = info.features.join(' • ');
            modelInfo.innerHTML = `
                <strong>${info.name}</strong><br>
                ${info.description}<br>
                <em>Features: ${features}</em>
            `;
            
            // Update duration options based on model
            updateDurationOptions(info.duration);
            
            // Update audio checkbox
            const audioCheckbox = document.getElementById('generateAudio');
            audioCheckbox.disabled = !info.audio;
            if (!info.audio) {
                audioCheckbox.checked = false;
            }
        }
    });
    
    // Trigger initial update
    modelSelect.dispatchEvent(new Event('change'));
}

function updateDurationOptions(allowedDurations) {
    const durationSelect = document.getElementById('duration');
    const currentValue = parseInt(durationSelect.value);
    
    // Enable/disable options
    Array.from(durationSelect.options).forEach(option => {
        const value = parseInt(option.value);
        if (allowedDurations.includes(value)) {
            option.disabled = false;
        } else {
            option.disabled = true;
        }
    });
    
    // Reset if current value not allowed
    if (!allowedDurations.includes(currentValue)) {
        durationSelect.value = allowedDurations[allowedDurations.length - 1];
    }
}

// Mode Switch (Text vs Image)
function setupModeSwitch() {
    const generationType = document.getElementById('generationType');
    const textMode = document.getElementById('textMode');
    const imageMode = document.getElementById('imageMode');
    
    generationType.addEventListener('change', (e) => {
        currentMode = e.target.value;
        
        if (currentMode === 'image-to-video') {
            textMode.style.display = 'none';
            imageMode.style.display = 'block';
        } else if (currentMode === 'text-to-video') {
            textMode.style.display = 'block';
            imageMode.style.display = 'none';
        }
    });
}

// File Upload
function setupFileUpload() {
    const uploadBox = document.getElementById('uploadBox');
    const fileInput = document.getElementById('imageFile');
    const placeholder = document.getElementById('uploadPlaceholder');
    const preview = document.getElementById('uploadPreview');
    
    uploadBox.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                placeholder.style.display = 'none';
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.add('active');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Drag and drop
    uploadBox.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadBox.style.borderColor = '#3b82f6';
    });
    
    uploadBox.addEventListener('dragleave', () => {
        uploadBox.style.borderColor = '';
    });
    
    uploadBox.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadBox.style.borderColor = '';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            fileInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });
}

// Accordion
function setupAccordion() {
    const btn = document.getElementById('settingsBtn');
    const panel = document.getElementById('settingsPanel');
    
    btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        panel.classList.toggle('show');
    });
}

// Form Submit
function setupForm() {
    const form = document.getElementById('videoForm');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await generateVideo();
    });
}

// Generate Video
async function generateVideo() {
    const form = document.getElementById('videoForm');
    const generateBtn = document.getElementById('generateBtn');
    const progressBox = document.getElementById('progressBox');
    const resultsBox = document.getElementById('resultsBox');
    
    // Get credentials
    const projectId = document.getElementById('projectId').value;
    const accessToken = document.getElementById('accessToken').value;
    const projectIdJson = document.getElementById('projectIdJson').value;
    const serviceAccountJson = document.getElementById('serviceAccountJson').value;
    
    // Validate credentials
    if (!projectId && !projectIdJson) {
        showNotification('Please enter your Google Cloud Project ID', 'error');
        return;
    }
    
    if (!accessToken && !serviceAccountJson) {
        showNotification('Please provide either Access Token or Service Account JSON', 'error');
        return;
    }
    
    // Prepare data
    const formData = {
        model: currentModel,
        mode: currentMode,
        
        // Credentials
        projectId: projectId || projectIdJson,
        accessToken: accessToken,
        serviceAccountJson: serviceAccountJson,
        location: 'us-central1', // Default location
        
        // Video settings
        aspectRatio: document.getElementById('aspectRatio').value,
        duration: parseInt(document.getElementById('duration').value),
        resolution: document.getElementById('resolution').value,
        sampleCount: parseInt(document.getElementById('samples').value),
        compressionQuality: document.getElementById('compression').value,
        enhancePrompt: document.getElementById('enhancePrompt').checked,
        generateAudio: document.getElementById('generateAudio').checked,
        enableAdult: document.getElementById('enableAdult').checked
    };
    
    if (currentMode === 'text-to-video') {
        formData.prompt = document.getElementById('prompt').value;
        formData.negativePrompt = document.getElementById('negativePrompt').value;
        
        if (!formData.prompt) {
            showNotification('Please enter a prompt', 'error');
            return;
        }
    } else {
        const imageFile = document.getElementById('imageFile').files[0];
        if (!imageFile) {
            showNotification('Please upload an image', 'error');
            return;
        }
        
        // Convert to base64
        formData.image = await fileToBase64(imageFile);
        formData.mimeType = imageFile.type;
        formData.prompt = document.getElementById('imagePrompt').value;
    }
    
    // Show progress
    generateBtn.disabled = true;
    generateBtn.innerHTML = '<span class="spinner"></span> Generating...';
    progressBox.style.display = 'block';
    resultsBox.style.display = 'none';
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to start generation');
        }
        
        // Start polling
        currentOperation = result.operationName;
        startPolling();
        
        showNotification('Video generation started!', 'success');
        
    } catch (error) {
        console.error('Error:', error);
        showNotification(error.message, 'error');
        resetGenerateButton();
        progressBox.style.display = 'none';
    }
}

// Polling
function startPolling() {
    let progress = 10;
    updateProgress(progress, 'Processing your request...');
    
    // Simulate progress
    const progressSim = setInterval(() => {
        if (progress < 90) {
            progress += 5;
            updateProgress(progress, 'Generating video...');
        }
    }, 2000);
    
    // Poll for results
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(POLL_ENDPOINT, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ operationName: currentOperation })
            });
            
            const result = await response.json();
            
            if (result.done) {
                clearInterval(pollingInterval);
                clearInterval(progressSim);
                updateProgress(100, 'Complete!');
                
                setTimeout(() => {
                    displayResults(result.videos);
                    resetGenerateButton();
                }, 500);
            }
        } catch (error) {
            console.error('Polling error:', error);
            clearInterval(pollingInterval);
            clearInterval(progressSim);
            showNotification('Error checking status', 'error');
            resetGenerateButton();
        }
    }, 5000);
}

function updateProgress(percent, message) {
    const fill = document.getElementById('progressFill');
    const percentEl = document.getElementById('progressPercent');
    const messageEl = document.getElementById('progressMessage');
    
    fill.style.width = percent + '%';
    percentEl.textContent = Math.round(percent) + '%';
    messageEl.textContent = message;
}

function displayResults(videos) {
    const resultsBox = document.getElementById('resultsBox');
    const progressBox = document.getElementById('progressBox');
    const videoResults = document.getElementById('videoResults');
    
    progressBox.style.display = 'none';
    resultsBox.style.display = 'block';
    
    videoResults.innerHTML = '';
    
    if (!videos || videos.length === 0) {
        videoResults.innerHTML = '<p class="empty-state">No videos generated</p>';
        return;
    }
    
    videos.forEach((video, index) => {
        const card = document.createElement('div');
        card.className = 'video-card';
        
        const videoUrl = video.url || video.gcsUri;
        
        card.innerHTML = `
            <video controls>
                <source src="${videoUrl}" type="${video.mimeType || 'video/mp4'}">
            </video>
            <div class="video-actions">
                <button class="btn-secondary" onclick="downloadVideo('${videoUrl}', 'video-${index + 1}.mp4')">
                    📥 Download
                </button>
                <button class="btn-secondary" onclick="shareVideo('${videoUrl}')">
                    🔗 Share
                </button>
            </div>
        `;
        
        videoResults.appendChild(card);
    });
    
    // Reload gallery
    loadGallery();
}

function resetGenerateButton() {
    const generateBtn = document.getElementById('generateBtn');
    generateBtn.disabled = false;
    generateBtn.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23 7l-7 5 7 5V7z" stroke="currentColor" stroke-width="2"/>
            <rect x="1" y="5" width="15" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
        </svg>
        Generate Video
    `;
}

function resetForm() {
    document.getElementById('videoForm').reset();
    document.getElementById('resultsBox').style.display = 'none';
    document.getElementById('progressBox').style.display = 'none';
    document.getElementById('uploadPreview').classList.remove('active');
    document.getElementById('uploadPlaceholder').style.display = 'block';
    resetGenerateButton();
}

// Gallery
async function loadGallery() {
    const galleryGrid = document.getElementById('galleryGrid');
    
    try {
        const response = await fetch(GALLERY_ENDPOINT);
        const videos = await response.json();
        
        if (videos && videos.length > 0) {
            galleryGrid.innerHTML = '';
            videos.slice(0, 6).forEach((video, index) => {
                const card = document.createElement('div');
                card.className = 'video-card';
                
                const videoUrl = video.url || video.gcsUri;
                
                card.innerHTML = `
                    <video controls>
                        <source src="${videoUrl}" type="${video.mimeType || 'video/mp4'}">
                    </video>
                    <div class="video-actions">
                        <button class="btn-secondary" onclick="downloadVideo('${videoUrl}', 'video-${index + 1}.mp4')">
                            📥
                        </button>
                    </div>
                `;
                
                galleryGrid.appendChild(card);
            });
        } else {
            galleryGrid.innerHTML = '<p class="empty-state">No videos yet. Generate your first one!</p>';
        }
    } catch (error) {
        console.error('Gallery error:', error);
    }
}

// Utilities
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result.split(',')[1]);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

function downloadVideo(url, filename) {
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    showNotification('Download started', 'success');
}

function shareVideo(url) {
    if (navigator.share) {
        navigator.share({ url }).then(() => {
            showNotification('Shared successfully', 'success');
        }).catch(() => {
            copyToClipboard(url);
        });
    } else {
        copyToClipboard(url);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Link copied to clipboard', 'success');
    });
}

function showNotification(message, type = 'info') {
    // Simple alert for now
    alert(message);
}

// Export functions
window.downloadVideo = downloadVideo;
window.shareVideo = shareVideo;
window.resetForm = resetForm;
