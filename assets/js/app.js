// App Configuration
const API_ENDPOINT = 'api/generate.php';
const POLL_ENDPOINT = 'api/poll.php';
const GALLERY_ENDPOINT = 'api/gallery.php';

// State Management
let currentOperation = null;
let pollingInterval = null;

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    // Setup navigation
    setupNavigation();
    
    // Setup tabs
    setupTabs();
    
    // Setup forms
    setupTextToVideoForm();
    setupImageToVideoForm();
    
    // Setup file upload
    setupFileUpload();
    
    // Load gallery
    loadGallery();
    
    // Character counter
    setupCharacterCounter();
    
    // Smooth scroll
    setupSmoothScroll();
}

// ==================== NAVIGATION ====================
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = link.getAttribute('href');
            
            // Update active state
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            // Scroll to section
            if (target.startsWith('#')) {
                const section = document.querySelector(target);
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
}

function scrollToGenerate() {
    const generateSection = document.getElementById('generate');
    if (generateSection) {
        generateSection.scrollIntoView({ behavior: 'smooth' });
    }
}

// ==================== TABS ====================
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Update button states
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Update content visibility
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
}

// ==================== TEXT TO VIDEO FORM ====================
function setupTextToVideoForm() {
    const form = document.getElementById('textToVideoForm');
    
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                type: 'text-to-video',
                prompt: document.getElementById('prompt').value,
                negativePrompt: document.getElementById('negativePrompt').value,
                duration: parseInt(document.getElementById('duration').value),
                aspectRatio: document.getElementById('aspectRatio').value,
                resolution: document.getElementById('resolution').value,
                sampleCount: parseInt(document.getElementById('sampleCount').value),
                enhancePrompt: document.getElementById('enhancePrompt').checked,
                generateAudio: document.getElementById('generateAudio').checked
            };
            
            await generateVideo(formData);
        });
    }
}

// ==================== IMAGE TO VIDEO FORM ====================
function setupImageToVideoForm() {
    const form = document.getElementById('imageToVideoForm');
    
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const imageInput = document.getElementById('imageInput');
            const file = imageInput.files[0];
            
            if (!file) {
                showToast('Please upload an image first', 'error');
                return;
            }
            
            // Convert image to base64
            const base64Image = await fileToBase64(file);
            
            const formData = {
                type: 'image-to-video',
                image: base64Image,
                mimeType: file.type,
                prompt: document.getElementById('imagePrompt').value,
                duration: parseInt(document.getElementById('imageDuration').value),
                aspectRatio: document.getElementById('imageAspectRatio').value,
                resolution: document.getElementById('imageResolution').value,
                resizeMode: document.getElementById('resizeMode').value
            };
            
            await generateVideo(formData);
        });
    }
}

// ==================== FILE UPLOAD ====================
function setupFileUpload() {
    const uploadArea = document.getElementById('imageUploadArea');
    const fileInput = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');
    
    if (!uploadArea || !fileInput) return;
    
    // Click to upload
    uploadArea.addEventListener('click', (e) => {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });
    
    // File selection
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            handleFileUpload(file);
        }
    });
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--primary)';
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.borderColor = '';
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '';
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            fileInput.files = e.dataTransfer.files;
            handleFileUpload(file);
        } else {
            showToast('Please upload an image file', 'error');
        }
    });
}

function handleFileUpload(file) {
    const uploadArea = document.getElementById('imageUploadArea');
    const preview = document.getElementById('imagePreview');
    
    // Validate file size (10MB)
    if (file.size > 10 * 1024 * 1024) {
        showToast('File size must be less than 10MB', 'error');
        return;
    }
    
    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        preview.classList.add('active');
        uploadArea.classList.add('has-file');
    };
    reader.readAsDataURL(file);
}

function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
            const base64 = reader.result.split(',')[1];
            resolve(base64);
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

// ==================== VIDEO GENERATION ====================
async function generateVideo(formData) {
    try {
        // Show progress section
        const progressSection = document.getElementById('progressSection');
        const resultsSection = document.getElementById('resultsSection');
        
        progressSection.style.display = 'block';
        resultsSection.style.display = 'none';
        
        updateProgress(0, 'Initializing video generation...');
        
        // Send request to backend
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to start video generation');
        }
        
        // Start polling for results
        currentOperation = result.operationName;
        startPolling(currentOperation);
        
        showToast('Video generation started!', 'success');
        
    } catch (error) {
        console.error('Error generating video:', error);
        showToast(error.message || 'Failed to generate video', 'error');
        document.getElementById('progressSection').style.display = 'none';
    }
}

// ==================== POLLING ====================
function startPolling(operationName) {
    let progress = 10;
    
    // Simulate progress
    const progressInterval = setInterval(() => {
        if (progress < 90) {
            progress += 5;
            updateProgress(progress, 'Processing video generation...');
        }
    }, 2000);
    
    // Poll for results
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(POLL_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ operationName })
            });
            
            const result = await response.json();
            
            if (result.done) {
                clearInterval(pollingInterval);
                clearInterval(progressInterval);
                
                updateProgress(100, 'Video generation complete!');
                
                setTimeout(() => {
                    displayResults(result.videos);
                }, 1000);
            }
        } catch (error) {
            console.error('Polling error:', error);
            clearInterval(pollingInterval);
            clearInterval(progressInterval);
            showToast('Error checking video status', 'error');
        }
    }, 5000);
}

function updateProgress(percentage, message) {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressStatus = document.getElementById('progressStatus');
    
    if (progressBar) {
        progressBar.style.width = `${percentage}%`;
    }
    
    if (progressText) {
        progressText.textContent = message;
    }
    
    if (progressStatus) {
        if (percentage === 100) {
            progressStatus.textContent = 'Complete';
        } else {
            progressStatus.textContent = `${Math.round(percentage)}%`;
        }
    }
}

// ==================== RESULTS DISPLAY ====================
function displayResults(videos) {
    const progressSection = document.getElementById('progressSection');
    const resultsSection = document.getElementById('resultsSection');
    const resultsGrid = document.getElementById('resultsGrid');
    
    progressSection.style.display = 'none';
    resultsSection.style.display = 'block';
    
    resultsGrid.innerHTML = '';
    
    if (!videos || videos.length === 0) {
        resultsGrid.innerHTML = '<p style="color: var(--text-secondary);">No videos generated</p>';
        return;
    }
    
    videos.forEach((video, index) => {
        const videoCard = createVideoCard(video, index);
        resultsGrid.appendChild(videoCard);
    });
    
    // Scroll to results
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function createVideoCard(video, index) {
    const card = document.createElement('div');
    card.className = 'video-card';
    
    const videoUrl = video.url || video.gcsUri;
    
    card.innerHTML = `
        <video controls>
            <source src="${videoUrl}" type="${video.mimeType || 'video/mp4'}">
            Your browser does not support the video tag.
        </video>
        <div class="video-card-info">
            <div class="video-card-actions">
                <button class="icon-btn" onclick="downloadVideo('${videoUrl}', 'video-${index + 1}.mp4')" title="Download">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="icon-btn" onclick="shareVideo('${videoUrl}')" title="Share">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
                        <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                        <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
                        <path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// ==================== VIDEO ACTIONS ====================
function downloadVideo(url, filename) {
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    showToast('Download started', 'success');
}

function shareVideo(url) {
    if (navigator.share) {
        navigator.share({
            title: 'Check out this AI-generated video',
            url: url
        }).then(() => {
            showToast('Shared successfully', 'success');
        }).catch((error) => {
            copyToClipboard(url);
        });
    } else {
        copyToClipboard(url);
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Link copied to clipboard', 'success');
    }).catch(() => {
        showToast('Failed to copy link', 'error');
    });
}

// ==================== GALLERY ====================
async function loadGallery() {
    const galleryGrid = document.getElementById('galleryGrid');
    
    if (!galleryGrid) return;
    
    try {
        const response = await fetch(GALLERY_ENDPOINT);
        const videos = await response.json();
        
        if (videos && videos.length > 0) {
            galleryGrid.innerHTML = '';
            videos.forEach((video, index) => {
                const card = createVideoCard(video, index);
                galleryGrid.appendChild(card);
            });
        } else {
            galleryGrid.innerHTML = '<p style="color: var(--text-secondary); text-align: center; grid-column: 1/-1;">No videos in gallery yet. Create your first video!</p>';
        }
    } catch (error) {
        console.error('Error loading gallery:', error);
        galleryGrid.innerHTML = '<p style="color: var(--text-secondary); text-align: center; grid-column: 1/-1;">Failed to load gallery</p>';
    }
}

// ==================== UTILITIES ====================
function setupCharacterCounter() {
    const promptInput = document.getElementById('prompt');
    const charCount = document.getElementById('promptCharCount');
    
    if (promptInput && charCount) {
        promptInput.addEventListener('input', () => {
            charCount.textContent = promptInput.value.length;
        });
    }
}

function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = {
        success: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:var(--success)">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`,
        error: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:var(--error)">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>`,
        warning: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:var(--warning)">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`,
        info: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;color:var(--primary)">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
            <path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`
    };
    
    toast.innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;">
            ${icon[type] || icon.info}
            <span style="color:var(--text-primary)">${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out forwards';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Export functions to global scope
window.scrollToGenerate = scrollToGenerate;
window.downloadVideo = downloadVideo;
window.shareVideo = shareVideo;
