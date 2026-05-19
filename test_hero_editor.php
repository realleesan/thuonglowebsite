<?php
/**
 * Test Hero Editor Debug
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Hero Editor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test-container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
        .debug-info { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; margin: 10px 0; }
        .test-button { background: #356DF1; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin: 5px; }
        .test-button:hover { background: #2563eb; }
        
        /* Editor Styles */
        .custom-editor-toolbar { background: #fdfdfd; border: 1px solid #e0e0e0; border-bottom: none; border-radius: 8px 8px 0 0; padding: 10px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; }
        .toolbar-group { display: flex; gap: 5px; align-items: center; border-right: 1px solid #eee; padding-right: 15px; }
        .toolbar-group:last-child { border-right: none; }
        .custom-editor-toolbar button { background: white; border: 1px solid #ddd; border-radius: 6px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #555; }
        .custom-editor-toolbar button:hover { background: #f0f4ff; color: #356DF1; border-color: #356DF1; }
        .font-select { padding: 6px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; }
        .size-input-wrapper { display: flex; align-items: center; gap: 5px; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 0 8px; }
        .size-input { border: none; width: 60px; padding: 6px 4px; font-size: 14px; text-align: center; outline: none; font-weight: 600; }
        .size-input-wrapper span { font-size: 12px; color: #888; font-weight: 500; }
        .color-picker-wrapper { position: relative; width: 34px; height: 34px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
        .color-picker-wrapper input[type="color"] { position: absolute; top: -5px; left: -5px; width: 50px; height: 50px; cursor: pointer; opacity: 0; z-index: 2; }
        .color-picker-wrapper i { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1; color: #555; }
        .custom-editable-area { min-height: 150px; border: 1px solid #e0e0e0; border-radius: 0 0 8px 8px; padding: 20px; background: white; outline: none; font-size: 1rem; line-height: 1.6; }
        .custom-editable-area:focus { border-color: #356DF1; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Hero Editor Debug Test</h1>
        
        <div class="test-section">
            <div class="test-title">📝 Test Editor 1 - Title</div>
            
            <!-- Custom Toolbar -->
            <div class="custom-editor-toolbar" data-for="title_main">
                <div class="toolbar-group">
                    <button type="button" onclick="applyFormat('bold', 'title_main')" title="In đậm"><i class="fas fa-bold"></i></button>
                    <button type="button" onclick="applyFormat('italic', 'title_main')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                    <button type="button" onclick="applyFormat('underline', 'title_main')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                </div>
                <div class="toolbar-group">
                    <select onchange="applyStyle('fontFamily', this.value, 'title_main')" class="font-select">
                        <option value="">Font chữ</option>
                        <option value="Arial, sans-serif">Arial</option>
                        <option value="'Inter', sans-serif">Inter</option>
                        <option value="'Roboto', sans-serif">Roboto</option>
                        <option value="serif">Serif</option>
                        <option value="monospace">Monospace</option>
                    </select>
                    <div class="size-input-wrapper">
                        <input type="number" value="48" min="10" max="100" onchange="applyStyle('fontSize', this.value + 'px', 'title_main')" class="size-input">
                        <span>px</span>
                    </div>
                </div>
                <div class="toolbar-group">
                    <div class="color-picker-wrapper">
                        <input type="color" onchange="applyStyle('color', this.value, 'title_main')" title="Màu chữ">
                        <i class="fas fa-font"></i>
                    </div>
                    <button type="button" onclick="applyFormat('removeFormat', 'title_main')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                </div>
            </div>
            
            <!-- Editable Area -->
            <div id="editor-title_main" class="custom-editable-area" contenteditable="true" oninput="syncEditor('title_main')">
                Test Hero Title - Bôi đen và thử các format
            </div>
            <textarea id="title_main" name="title_main" style="display:none;"></textarea>
            
            <div class="debug-info" id="debug-title_main">
                Raw HTML: <br>
                <span id="raw-html-title_main"></span>
            </div>
        </div>
        
        <div class="test-section">
            <div class="test-title">📝 Test Editor 2 - Subtitle</div>
            
            <!-- Custom Toolbar -->
            <div class="custom-editor-toolbar" data-for="subtitle">
                <div class="toolbar-group">
                    <button type="button" onclick="applyFormat('bold', 'subtitle')" title="In đậm"><i class="fas fa-bold"></i></button>
                    <button type="button" onclick="applyFormat('italic', 'subtitle')" title="In nghiêng"><i class="fas fa-italic"></i></button>
                    <button type="button" onclick="applyFormat('underline', 'subtitle')" title="Gạch chân"><i class="fas fa-underline"></i></button>
                </div>
                <div class="toolbar-group">
                    <select onchange="applyStyle('fontFamily', this.value, 'subtitle')" class="font-select">
                        <option value="">Font chữ</option>
                        <option value="Arial, sans-serif">Arial</option>
                        <option value="'Inter', sans-serif">Inter</option>
                        <option value="'Roboto', sans-serif">Roboto</option>
                        <option value="serif">Serif</option>
                        <option value="monospace">Monospace</option>
                    </select>
                    <div class="size-input-wrapper">
                        <input type="number" value="18" min="10" max="100" onchange="applyStyle('fontSize', this.value + 'px', 'subtitle')" class="size-input">
                        <span>px</span>
                    </div>
                </div>
                <div class="toolbar-group">
                    <div class="color-picker-wrapper">
                        <input type="color" onchange="applyStyle('color', this.value, 'subtitle')" title="Màu chữ">
                        <i class="fas fa-font"></i>
                    </div>
                    <button type="button" onclick="applyFormat('removeFormat', 'subtitle')" title="Xóa định dạng"><i class="fas fa-eraser"></i></button>
                </div>
            </div>
            
            <!-- Editable Area -->
            <div id="editor-subtitle" class="custom-editable-area" contenteditable="true" oninput="syncEditor('subtitle')">
                Test Hero Subtitle - Bôi đen và thử các format
            </div>
            <textarea id="subtitle" name="subtitle" style="display:none;"></textarea>
            
            <div class="debug-info" id="debug-subtitle">
                Raw HTML: <br>
                <span id="raw-html-subtitle"></span>
            </div>
        </div>
        
        <div class="test-section">
            <div class="test-title">🧪 Test Operations</div>
            <button class="test-button" onclick="testBold()">Test Bold Toggle</button>
            <button class="test-button" onclick="testFontSize()">Test Font Size</button>
            <button class="test-button" onclick="testCleanHTML()">Test Clean HTML</button>
            <button class="test-button" onclick="testSubmit()">Test Submit Data</button>
            <button class="test-button" onclick="clearAll()">Clear All</button>
            
            <div class="debug-info" id="test-results">
                Test results will appear here...
            </div>
        </div>
        
        <div class="test-section">
            <div class="test-title">📊 Submit Data Preview</div>
            <div class="debug-info" id="submit-preview">
                Submit data will appear here...
            </div>
        </div>
    </div>

    <script>
        // Store editor instances
        window.editors = {};
        
        class RichTextEditor {
            constructor(fieldId) {
                this.fieldId = fieldId;
                this.editor = document.getElementById('editor-' + fieldId);
                this.textarea = document.getElementById(fieldId);
                this.toolbar = document.querySelector(`[data-for="${fieldId}"]`);
                
                this.init();
            }
            
            init() {
                // Initialize editor content
                if (this.textarea.value) {
                    this.editor.innerHTML = this.textarea.value;
                }
                
                // Add event listeners
                this.editor.addEventListener('input', () => this.syncEditor());
                this.editor.addEventListener('paste', (e) => this.handlePaste(e));
                this.editor.addEventListener('keydown', (e) => this.handleKeydown(e));
                this.editor.addEventListener('mouseup', () => this.updateToolbarState());
                this.editor.addEventListener('keyup', () => this.updateToolbarState());
                
                // Initialize toolbar buttons
                this.initToolbar();
                
                // Auto-save to textarea
                setInterval(() => this.syncEditor(), 1000);
                
                // Update debug info
                setInterval(() => this.updateDebugInfo(), 500);
            }
            
            initToolbar() {
                if (!this.toolbar) return;
                
                // Bold, Italic, Underline
                this.toolbar.querySelectorAll('button').forEach(btn => {
                    const onclick = btn.getAttribute('onclick');
                    if (onclick && onclick.includes('applyFormat')) {
                        const command = onclick.match(/'([^']+)'/)[1];
                        btn.onclick = () => this.applyFormat(command);
                    }
                });
                
                // Font family
                const fontSelect = this.toolbar.querySelector('select');
                if (fontSelect) {
                    fontSelect.onchange = () => {
                        if (fontSelect.value) {
                            this.applyStyle('fontFamily', fontSelect.value);
                        }
                    };
                }
                
                // Font size
                const sizeInput = this.toolbar.querySelector('input[type="number"]');
                if (sizeInput) {
                    sizeInput.onchange = () => {
                        const value = sizeInput.value;
                        if (value && value > 0) {
                            // Store current selection before applying style
                            const selection = window.getSelection();
                            const range = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;
                            const isCollapsed = range ? range.collapsed : true;
                            
                            this.applyStyle('fontSize', value + 'px');
                            
                            // Restore selection if it was lost
                            if (range && !isCollapsed) {
                                try {
                                    selection.removeAllRanges();
                                    selection.addRange(range);
                                } catch (e) {
                                    console.log('Could not restore selection:', e);
                                }
                            }
                        } else {
                            // If empty or invalid, remove font size style
                            this.removeStyle('fontSize');
                        }
                    };
                }
                
                // Color
                const colorInput = this.toolbar.querySelector('input[type="color"]');
                if (colorInput) {
                    colorInput.onchange = () => {
                        this.applyStyle('color', colorInput.value);
                    };
                }
                
                // Remove format
                const clearBtns = this.toolbar.querySelectorAll('button');
                clearBtns.forEach(btn => {
                    if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes('removeFormat')) {
                        btn.onclick = () => this.clearFormat();
                    }
                });
            }
            
            applyFormat(command) {
                this.editor.focus();
                
                try {
                    // Use built-in execCommand for reliable toggle behavior
                    const cmd = command === 'bold' ? 'bold' : 
                               command === 'italic' ? 'italic' : 'underline';
                    
                    document.execCommand(cmd, false, null);
                    
                    this.syncEditor();
                    this.updateToolbarState();
                } catch (e) {
                    console.error('Format error:', e);
                }
            }
            
            applyStyle(property, value) {
                this.editor.focus();
                
                try {
                    const selection = window.getSelection();
                    if (!selection.rangeCount) return;
                    
                    const range = selection.getRangeAt(0);
                    const wasCollapsed = range.collapsed;
                    
                    // If no selection, apply to entire editor content
                    if (wasCollapsed) {
                        // Apply style to the entire editor
                        this.applyStyleToEditor(property, value);
                    } else {
                        // Apply style to selected text
                        const span = document.createElement('span');
                        span.style[property] = value;
                        
                        try {
                            range.surroundContents(span);
                        } catch (e) {
                            // If range can't be surrounded, apply to each text node
                            this.applyStyleToSelection(property, value);
                        }
                    }
                    
                    this.syncEditor();
                    
                    // Restore selection if it existed before
                    if (!wasCollapsed) {
                        try {
                            selection.removeAllRanges();
                            selection.addRange(range);
                        } catch (e) {
                            console.log('Could not restore selection after style:', e);
                        }
                    }
                } catch (e) {
                    console.error('Style error:', e);
                }
            }
            
            applyStyleToEditor(property, value) {
                // Apply style to the entire editor content
                const content = this.editor.innerHTML;
                
                // Wrap entire content in span with style
                const wrapper = document.createElement('div');
                wrapper.innerHTML = content;
                
                const span = document.createElement('span');
                span.style[property] = value;
                
                // Move all child nodes to span
                while (wrapper.firstChild) {
                    span.appendChild(wrapper.firstChild);
                }
                
                // Clear editor and add styled content
                this.editor.innerHTML = '';
                this.editor.appendChild(span);
            }
            
            applyStyleToSelection(property, value) {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;
                
                const range = selection.getRangeAt(0);
                const contents = range.extractContents();
                
                // Process each node in selection
                const walker = document.createTreeWalker(
                    contents,
                    NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT,
                    null,
                    false
                );
                
                const nodes = [];
                let node;
                while (node = walker.nextNode()) {
                    nodes.push(node);
                }
                
                nodes.forEach(node => {
                    if (node.nodeType === Node.TEXT_NODE) {
                        const span = document.createElement('span');
                        span.style[property] = value;
                        span.textContent = node.textContent;
                        node.parentNode.replaceChild(span, node);
                    }
                });
                
                range.insertNode(contents);
            }
            
            removeStyle(property) {
                this.editor.focus();
                
                try {
                    const selection = window.getSelection();
                    if (!selection.rangeCount) return;
                    
                    const range = selection.getRangeAt(0);
                    
                    if (range.collapsed) {
                        // Remove style from entire editor
                        this.removeStyleFromEditor(property);
                    } else {
                        // Remove style from selected text
                        this.removeStyleFromSelection(property);
                    }
                    
                    this.syncEditor();
                } catch (e) {
                    console.error('Remove style error:', e);
                }
            }
            
            removeStyleFromEditor(property) {
                // Remove style from all elements in editor
                const elements = this.editor.querySelectorAll('*');
                elements.forEach(element => {
                    if (element.style[property]) {
                        element.style.removeProperty(property);
                    }
                });
                
                // Clean up empty spans
                this.cleanUpEmptySpans();
            }
            
            removeStyleFromSelection(property) {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;
                
                const range = selection.getRangeAt(0);
                const contents = range.extractContents();
                
                // Process each node in selection
                const walker = document.createTreeWalker(
                    contents,
                    NodeFilter.SHOW_ELEMENT,
                    null,
                    false
                );
                
                const nodes = [];
                let node;
                while (node = walker.nextNode()) {
                    nodes.push(node);
                }
                
                nodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE && node.style[property]) {
                        node.style.removeProperty(property);
                    }
                });
                
                range.insertNode(contents);
            }
            
            cleanUpEmptySpans() {
                // Remove empty spans and move their content to parent
                const emptySpans = this.editor.querySelectorAll('span');
                emptySpans.forEach(span => {
                    if (!span.style.cssText && span.textContent.trim() === '') {
                        span.remove();
                    } else if (!span.style.cssText) {
                        // Move content out of span
                        while (span.firstChild) {
                            span.parentNode.insertBefore(span.firstChild, span);
                        }
                        span.remove();
                    }
                });
            }
            
            clearFormat() {
                this.editor.focus();
                
                try {
                    const selection = window.getSelection();
                    if (!selection.rangeCount) return;
                    
                    const range = selection.getRangeAt(0);
                    
                    if (range.collapsed) {
                        // Clear format from entire editor
                        this.clearFormatFromEditor();
                    } else {
                        // Clear format from selected text
                        this.clearFormatFromSelection();
                    }
                    
                    this.syncEditor();
                    this.updateToolbarState();
                } catch (e) {
                    console.error('Clear format error:', e);
                }
            }
            
            clearFormatFromEditor() {
                // Get all formatted elements and unwrap them, but keep text
                const formattedElements = this.editor.querySelectorAll('strong, em, u, span');
                
                formattedElements.forEach(element => {
                    while (element.firstChild) {
                        element.parentNode.insertBefore(element.firstChild, element);
                    }
                    element.remove();
                });
                
                this.syncEditor();
            }
            
            clearFormatFromSelection() {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;
                
                const range = selection.getRangeAt(0);
                const contents = range.extractContents();
                
                // Remove all formatting from selection, but keep text
                const formattedElements = contents.querySelectorAll('strong, em, u, span');
                
                formattedElements.forEach(element => {
                    while (element.firstChild) {
                        element.parentNode.insertBefore(element.firstChild, element);
                    }
                    element.remove();
                });
                
                range.insertNode(contents);
                this.syncEditor();
            }
            
            handlePaste(e) {
                e.preventDefault();
                
                // Get plain text
                const text = e.clipboardData.getData('text/plain') || '';
                
                // Insert text at cursor
                const selection = window.getSelection();
                if (selection.rangeCount) {
                    const range = selection.getRangeAt(0);
                    range.deleteContents();
                    range.insertNode(document.createTextNode(text));
                }
                
                this.syncEditor();
            }
            
            handleKeydown(e) {
                // Handle keyboard shortcuts
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key) {
                        case 'b':
                            e.preventDefault();
                            this.applyFormat('bold');
                            break;
                        case 'i':
                            e.preventDefault();
                            this.applyFormat('italic');
                            break;
                        case 'u':
                            e.preventDefault();
                            this.applyFormat('underline');
                            break;
                    }
                }
            }
            
            updateToolbarState() {
                if (!this.toolbar) return;
                
                try {
                    const selection = window.getSelection();
                    if (!selection.rangeCount) return;
                    
                    const range = selection.getRangeAt(0);
                    if (range.collapsed) return;
                    
                    // Get selected element
                    let selectedElement = range.commonAncestorContainer;
                    if (selectedElement.nodeType === Node.TEXT_NODE) {
                        selectedElement = selectedElement.parentElement;
                    }
                    
                    // Update font family select
                    const fontSelect = this.toolbar.querySelector('select');
                    if (fontSelect && selectedElement) {
                        const fontFamily = window.getComputedStyle(selectedElement).fontFamily;
                        fontSelect.value = '';
                        
                        // Find matching option
                        Array.from(fontSelect.options).forEach(option => {
                            if (option.value && fontFamily.includes(option.value.replace(/['"]/g, ''))) {
                                fontSelect.value = option.value;
                            }
                        });
                    }
                    
                    // Update font size input
                    const sizeInput = this.toolbar.querySelector('input[type="number"]');
                    if (sizeInput && selectedElement) {
                        const fontSize = window.getComputedStyle(selectedElement).fontSize;
                        const fontSizeValue = parseInt(fontSize);
                        if (!isNaN(fontSizeValue) && fontSizeValue >= 10 && fontSizeValue <= 100) {
                            sizeInput.value = fontSizeValue;
                        } else {
                            sizeInput.value = ''; // Don't auto-fill invalid values
                        }
                    }
                    
                    // Update color picker
                    const colorInput = this.toolbar.querySelector('input[type="color"]');
                    if (colorInput && selectedElement) {
                        const color = window.getComputedStyle(selectedElement).color;
                        const hexColor = this.rgbToHex(color);
                        if (hexColor) {
                            colorInput.value = hexColor;
                        }
                    }
                    
                    // Update button states
                    this.updateButtonStates(selectedElement);
                    
                } catch (e) {
                    console.error('Toolbar state update error:', e);
                }
            }
            
            updateButtonStates(element) {
                if (!this.toolbar) return;
                
                // Update bold button using queryCommandState
                const boldBtn = this.toolbar.querySelector('button[title*="đậm"], button[title*="Bold"]');
                if (boldBtn) {
                    const isBold = document.queryCommandState('bold');
                    boldBtn.style.backgroundColor = isBold ? '#e0e0e0' : '';
                }
                
                // Update italic button using queryCommandState
                const italicBtn = this.toolbar.querySelector('button[title*="nghiêng"], button[title*="Italic"]');
                if (italicBtn) {
                    const isItalic = document.queryCommandState('italic');
                    italicBtn.style.backgroundColor = isItalic ? '#e0e0e0' : '';
                }
                
                // Update underline button using queryCommandState
                const underlineBtn = this.toolbar.querySelector('button[title*="chân"], button[title*="Underline"]');
                if (underlineBtn) {
                    const isUnderline = document.queryCommandState('underline');
                    underlineBtn.style.backgroundColor = isUnderline ? '#e0e0e0' : '';
                }
            }
            
            rgbToHex(rgb) {
                if (!rgb || rgb.indexOf('rgb') !== 0) return rgb;
                
                const values = rgb.match(/\d+/g);
                if (!values || values.length < 3) return rgb;
                
                const r = parseInt(values[0]);
                const g = parseInt(values[1]);
                const b = parseInt(values[2]);
                
                return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
            }
            
            syncEditor() {
                // Clean up HTML and sync to textarea
                let html = this.editor.innerHTML;
                
                // Clean up empty spans and invalid styles
                html = html.replace(/<span[^>]*><\/span>/g, '');
                html = html.replace(/<span[^>]*style\s*=\s*["'][^"']*["'][^>]*>(.*?)<\/span>/g, (match, content) => {
                    // Keep span only if it has valid styles
                    const styleMatch = match.match(/style\s*=\s*["']([^"']*)["']/);
                    if (styleMatch && styleMatch[1].trim()) {
                        return match;
                    }
                    return content; // Remove span if no valid styles
                });
                
                // Remove invalid font-size values
                html = html.replace(/font-size:\s*[^p;]*px;/g, '');
                
                this.textarea.value = html;
            }
            
            updateDebugInfo() {
                const rawHtml = this.editor.innerHTML;
                const debugElement = document.getElementById('raw-html-' + this.fieldId);
                if (debugElement) {
                    debugElement.textContent = rawHtml;
                }
            }
        }
        
        // Initialize editors
        document.addEventListener('DOMContentLoaded', function() {
            window.editors.title_main = new RichTextEditor('title_main');
            window.editors.subtitle = new RichTextEditor('subtitle');
            
            console.log('Rich text editors initialized:', Object.keys(window.editors));
        });
        
        // Legacy functions for backward compatibility
        function applyFormat(command, field) {
            const editor = window.editors?.[field];
            if (editor) {
                editor.applyFormat(command);
            }
        }
        
        function applyStyle(property, value, field) {
            const editor = window.editors?.[field];
            if (editor) {
                editor.applyStyle(property, value);
            }
        }
        
        function syncEditor(field) {
            const editor = window.editors?.[field];
            if (editor) {
                editor.syncEditor();
            }
        }
        
        // Test functions
        function testBold() {
            const results = document.getElementById('test-results');
            results.innerHTML = 'Testing bold toggle...';
            
            // Select some text
            const editor = document.getElementById('editor-title_main');
            const range = document.createRange();
            range.selectNodeContents(editor);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Apply bold
            window.editors.title_main.applyFormat('bold');
            
            setTimeout(() => {
                results.innerHTML += '<br>Bold applied. HTML: ' + editor.innerHTML;
            }, 100);
        }
        
        function testFontSize() {
            const results = document.getElementById('test-results');
            results.innerHTML = 'Testing font size...';
            
            // Select some text
            const editor = document.getElementById('editor-title_main');
            const range = document.createRange();
            range.selectNodeContents(editor);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Apply font size
            window.editors.title_main.applyStyle('fontSize', '24px');
            
            setTimeout(() => {
                results.innerHTML += '<br>Font size applied. HTML: ' + editor.innerHTML;
            }, 100);
        }
        
        function testCleanHTML() {
            const results = document.getElementById('test-results');
            results.innerHTML = 'Testing HTML cleaning...';
            
            // Sync both editors
            window.editors.title_main.syncEditor();
            window.editors.subtitle.syncEditor();
            
            const titleHtml = document.getElementById('title_main').value;
            const subtitleHtml = document.getElementById('subtitle').value;
            
            results.innerHTML += '<br>Title HTML: ' + titleHtml;
            results.innerHTML += '<br>Subtitle HTML: ' + subtitleHtml;
        }
        
        function testSubmit() {
            const preview = document.getElementById('submit-preview');
            
            // Clean HTML content
            function cleanHtml(html) {
                return html
                    // Fix invalid font-size values (only keep valid px values)
                    .replace(/font-size:\s*(\d+)px;/g, 'font-size: $1px;')
                    // Remove invalid font-size (non-numeric)
                    .replace(/font-size:\s*[^0-9p;]*px;/g, '')
                    // Remove font-size without px
                    .replace(/font-size:\s*\d+;?/g, '')
                    // Remove empty spans
                    .replace(/<span[^>]*><\/span>/g, '')
                    // Remove spans with empty or invalid styles
                    .replace(/<span[^>]*style\s*=\s*["'][^"']*["'][^>]*>(.*?)<\/span>/g, (match, content) => {
                        const styleMatch = match.match(/style\s*=\s*["']([^"']*)["']/);
                        if (styleMatch && styleMatch[1].trim()) {
                            // Check if style has valid properties
                            const style = styleMatch[1];
                            const hasValidStyle = /font-size:\s*\d+px;|color:\s*#[0-9a-fA-F]{6};|font-family:\s*[^;]+;/.test(style);
                            if (hasValidStyle) {
                                return match;
                            }
                        }
                        return content;
                    })
                    // Remove style attributes with only invalid properties
                    .replace(/style\s*=\s*["'][^"']*["']/g, (match) => {
                        const styleMatch = match.match(/style\s*=\s*["']([^"']*)["']/);
                        if (styleMatch && styleMatch[1].trim()) {
                            const style = styleMatch[1];
                            const hasValidStyle = /font-size:\s*\d+px;|color:\s*#[0-9a-fA-F]{6};|font-family:\s*[^;]+;/.test(style);
                            if (hasValidStyle) {
                                return match;
                            }
                        }
                        return '';
                    })
                    .trim();
            }
            
            const data = {
                title_main: cleanHtml(document.getElementById('editor-title_main').innerHTML),
                subtitle: cleanHtml(document.getElementById('editor-subtitle').innerHTML),
                image_url: '',
                background_color: '#ffffff',
                is_active: 1
            };
            
            preview.innerHTML = '<strong>Submit Data:</strong><br>' + 
                               JSON.stringify(data, null, 2);
            
            console.log('Submit data:', data);
        }
        
        function clearAll() {
            document.getElementById('editor-title_main').innerHTML = 'Test Hero Title - Bôi đen và thử các format';
            document.getElementById('editor-subtitle').innerHTML = 'Test Hero Subtitle - Bôi đen và thử các format';
            document.getElementById('test-results').innerHTML = 'Cleared all content';
            document.getElementById('submit-preview').innerHTML = 'Submit data will appear here...';
        }
    </script>
</body>
</html>
