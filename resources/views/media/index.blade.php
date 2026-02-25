@extends('layouts.app')

@section('content')

<div class="flex h-[calc(100vh-56px)] overflow-hidden" x-data="mediaManager()" x-init="init()">

    {{-- ============================================================
         LEFT — Main panel
    ================================================================ --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden bg-[#f0f0f1]">

        {{-- ── Toolbar ── --}}
        <div class="flex flex-wrap items-center gap-3 px-5 py-3 bg-white border-b border-gray-200 shrink-0">
            <h1 class="text-lg font-semibold text-gray-800 mr-1">Media Library</h1>

            {{-- Counts badge --}}
            <span class="text-xs text-gray-400 bg-gray-100 rounded-full px-2 py-0.5" x-text="filteredItems.length + ' items'"></span>

            <div class="flex items-center gap-2 ml-auto">
                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    <input x-model.debounce.300ms="search" type="search" placeholder="Search…"
                           class="pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 w-44">
                </div>

                {{-- Type filter --}}
                <select x-model="typeFilter"
                        class="text-sm border border-gray-300 rounded-md py-1.5 px-2 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">All media</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                    <option value="document">Documents</option>
                </select>

                {{-- Upload Button --}}
                <button @click="showUploader = !showUploader"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New
                </button>
            </div>
        </div>

        {{-- ── Upload Zone ── --}}
        <div x-show="showUploader"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="bg-white border-b border-gray-200 px-5 py-4 shrink-0">
            <form action="{{ route('media.store') }}" id="mediaDropzone" class="dropzone !border-2 !border-dashed !border-indigo-300 !rounded-xl !bg-indigo-50/60 !min-h-[120px] hover:!bg-indigo-50 !transition-colors">
                @csrf
                <div class="dz-message needsclick text-center">
                    <div class="flex flex-col items-center justify-center gap-1 py-4">
                        <svg class="w-10 h-10 text-indigo-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700">Drop files here or <span class="text-indigo-600 underline">click to upload</span></p>
                        <p class="text-xs text-gray-400">Maximum file size: 50 MB</p>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Grid scroll area ── --}}
        <div class="flex-1 overflow-y-auto p-5">

            {{-- Loading --}}
            <div x-show="loading" class="flex justify-center py-16">
                <svg class="animate-spin w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </div>

            {{-- Empty state --}}
            <div x-show="!loading && filteredItems.length === 0" class="flex flex-col items-center justify-center py-24 text-center" style="display:none">
                <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="font-medium text-gray-500">No media files yet</p>
                <p class="text-sm text-gray-400 mt-1">Upload files to get started</p>
                <button @click="showUploader = true" class="mt-4 px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                    Upload Files
                </button>
            </div>

            {{-- Media grid --}}
            <div x-show="!loading && filteredItems.length > 0"
                 class="grid gap-2"
                 style="grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));">
                <template x-for="item in filteredItems" :key="item.id">
                    <div @click="selectItem(item)"
                         class="group relative cursor-pointer rounded-sm overflow-hidden bg-white shadow-sm hover:shadow-md transition-all duration-200"
                         style="aspect-ratio: 1"
                         :style="selected?.id === item.id ? 'outline: 3px solid #4f46e5; outline-offset: 2px;' : ''">

                        {{-- Image thumbnail --}}
                        <template x-if="isImage(item.mime_type)">
                            <img :src="item.url" :alt="item.alt_text || item.name"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </template>

                        {{-- Video --}}
                        <template x-if="isVideo(item.mime_type)">
                            <div class="w-full h-full flex flex-col items-center justify-center bg-violet-50">
                                <svg class="w-10 h-10 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.878v6.243a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                                </svg>
                                <span class="text-xs text-violet-500 font-semibold mt-1 uppercase" x-text="ext(item.name)"></span>
                            </div>
                        </template>

                        {{-- Document --}}
                        <template x-if="!isImage(item.mime_type) && !isVideo(item.mime_type)">
                            <div class="w-full h-full flex flex-col items-center justify-center bg-sky-50">
                                <svg class="w-10 h-10 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs text-sky-500 font-semibold mt-1 uppercase" x-text="ext(item.name)"></span>
                            </div>
                        </template>

                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-200 flex items-end">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 w-full bg-gradient-to-t from-black/60 to-transparent p-2">
                                <p class="text-xs text-white truncate leading-tight" x-text="item.name"></p>
                            </div>
                        </div>

                        {{-- Selection checkmark --}}
                        <div x-show="selected?.id === item.id"
                             class="absolute top-1 right-1 w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shadow-md">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ============================================================
         RIGHT — Details Sidebar
    ================================================================ --}}
    <div class="shrink-0 bg-white border-l border-gray-200 flex flex-col overflow-hidden transition-all duration-300"
         :style="selected ? 'width:320px' : 'width:0px'">

        <div class="flex flex-col h-full" style="min-width:320px">

            {{-- Sidebar header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50 shrink-0">
                <span class="text-sm font-semibold text-gray-700">Attachment Details</span>
                <button @click="selected = null"
                        class="w-6 h-6 flex items-center justify-center rounded hover:bg-gray-200 text-gray-400 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto" x-show="selected">

                {{-- Preview  --}}
                <div class="h-44 bg-[#f0f0f1] flex items-center justify-center border-b border-gray-100 overflow-hidden">
                    <template x-if="selected && isImage(selected.mime_type)">
                        <img :src="selected.url" :alt="selected.name" class="max-h-full max-w-full object-contain p-3">
                    </template>
                    <template x-if="selected && isVideo(selected.mime_type)">
                        <svg class="w-16 h-16 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.878v6.243a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                        </svg>
                    </template>
                    <template x-if="selected && !isImage(selected.mime_type) && !isVideo(selected.mime_type)">
                        <svg class="w-16 h-16 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </template>
                </div>

                {{-- Meta info --}}
                <div class="px-4 py-3 space-y-0.5 border-b border-gray-100 text-xs">
                    <p class="font-semibold text-gray-800 text-sm break-all leading-snug" x-text="selected?.name"></p>
                    <p class="text-gray-400" x-text="humanDate(selected?.created_at)"></p>
                    <p class="text-gray-400" x-text="humanSize(selected?.size)"></p>
                    <p class="text-gray-400" x-text="selected?.mime_type"></p>
                </div>

                {{-- Copy URL --}}
                <div class="px-4 py-3 border-b border-gray-100">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">File URL</label>
                    <div class="flex gap-1.5">
                        <input :value="selected?.url" readonly
                               class="flex-1 min-w-0 text-xs border border-gray-200 rounded px-2 py-1.5 bg-gray-50 text-gray-500 truncate focus:outline-none">
                        <button @click="copyUrl()"
                                class="shrink-0 text-xs px-2 py-1.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-200 hover:border-indigo-200 rounded transition-colors">
                            <span x-text="copied ? '✓' : 'Copy'"></span>
                        </button>
                    </div>
                </div>

                {{-- Metadata editor --}}
                <form @submit.prevent="saveMetadata()" class="px-4 py-4 space-y-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide pb-1">Edit Details</p>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alt Text <span class="text-gray-400 font-normal">(for accessibility)</span></label>
                        <input x-model="form.alt_text" type="text" placeholder="Describe what's in the image"
                               class="w-full text-sm border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Caption</label>
                        <input x-model="form.caption" type="text" placeholder="Short caption text"
                               class="w-full text-sm border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                        <textarea x-model="form.description" rows="3" placeholder="Longer description…"
                                  class="w-full text-sm border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent bg-gray-50 resize-none"></textarea>
                    </div>

                    <button type="submit"
                            :disabled="saving"
                            class="w-full py-2 text-sm font-medium rounded-md text-white transition-colors"
                            :class="saving ? 'bg-indigo-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'">
                        <span x-show="!saving && !saveSuccess">Save Changes</span>
                        <span x-show="saving">Saving…</span>
                        <span x-show="saveSuccess && !saving" class="text-green-100">✓ Saved</span>
                    </button>
                </form>

                {{-- Delete --}}
                <div class="px-4 pb-6 pt-1">
                    <hr class="border-gray-100 mb-4">
                    <button @click="deleteSelected()"
                            class="w-full py-2 text-sm font-medium rounded-md text-red-600 border border-red-200 hover:bg-red-50 hover:border-red-300 transition-colors">
                        Delete permanently
                    </button>
                    <p class="text-xs text-center text-gray-400 mt-2">This action cannot be undone</p>
                </div>

            </div>{{-- /scrollable body --}}
        </div>
    </div>{{-- /sidebar --}}

</div>{{-- /root --}}
@endsection

@push('scripts')
<script>
const INITIAL_MEDIA = {!! json_encode($medias->values()) !!};
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

document.addEventListener('alpine:init', () => {
    Alpine.data('mediaManager', () => ({
        allItems:     INITIAL_MEDIA,
        selected:     null,
        search:       '',
        typeFilter:   '',
        showUploader: false,
        loading:      false,
        saving:       false,
        saveSuccess:  false,
        copied:       false,
        form: { alt_text: '', caption: '', description: '' },

        get filteredItems() {
            let items = [...this.allItems];
            const s = this.search.trim().toLowerCase();
            if (s) items = items.filter(i => i.name.toLowerCase().includes(s));
            if (this.typeFilter === 'image')    items = items.filter(i => i.mime_type?.startsWith('image/'));
            if (this.typeFilter === 'video')    items = items.filter(i => i.mime_type?.startsWith('video/'));
            if (this.typeFilter === 'document') items = items.filter(i =>
                i.mime_type?.startsWith('application/') || i.mime_type?.startsWith('text/'));
            return items;
        },

        init() {
            Dropzone.autoDiscover = false;
            const vm = this;
            const dz = new Dropzone('#mediaDropzone', {
                paramName: 'file',
                maxFilesize: 50,
                headers: { 'X-CSRF-TOKEN': CSRF },
            });
            dz.on('success', (file, res) => {
                if (res.success) {
                    vm.allItems.unshift(res.media);
                    setTimeout(() => dz.removeFile(file), 1500);
                }
            });
            dz.on('error', (file, msg) => console.error('Upload error:', msg));
        },

        selectItem(item) {
            this.selected    = item;
            this.saveSuccess = false;
            this.form = {
                alt_text:    item.alt_text    ?? '',
                caption:     item.caption     ?? '',
                description: item.description ?? '',
            };
        },

        async saveMetadata() {
            if (!this.selected) return;
            this.saving = true;
            this.saveSuccess = false;
            try {
                const res  = await fetch(`/media/${this.selected.id}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (data.success) {
                    const idx = this.allItems.findIndex(i => i.id === this.selected.id);
                    if (idx !== -1) {
                        Object.assign(this.allItems[idx], this.form);
                        this.selected = this.allItems[idx];
                    }
                    this.saveSuccess = true;
                    setTimeout(() => this.saveSuccess = false, 3000);
                }
            } finally {
                this.saving = false;
            }
        },

        async deleteSelected() {
            if (!this.selected) return;
            if (!confirm(`Delete "${this.selected.name}" permanently? This action cannot be undone.`)) return;
            const id = this.selected.id;
            const res = await fetch(`/media/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            if (res.ok) {
                this.allItems  = this.allItems.filter(i => i.id !== id);
                this.selected  = null;
            }
        },

        async copyUrl() {
            if (!this.selected?.url) return;
            await navigator.clipboard.writeText(this.selected.url);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },

        isImage(m) { return m?.startsWith('image/'); },
        isVideo(m)  { return m?.startsWith('video/'); },
        ext(name)   { return (name?.split('.').pop() ?? '').toUpperCase(); },

        humanSize(b) {
            if (!b) return '0 B';
            const u = ['B','KB','MB','GB'];
            const i = Math.floor(Math.log(b) / Math.log(1024));
            return parseFloat((b / Math.pow(1024, i)).toFixed(1)) + ' ' + u[i];
        },
        humanDate(d) {
            if (!d) return '';
            return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        },
    }));
});
</script>

<style>
    /* Dropzone overrides */
    #mediaDropzone .dz-preview { margin: 6px; }
    #mediaDropzone .dz-preview .dz-image { border-radius: 6px; }
    #mediaDropzone .dz-preview .dz-progress { display: none; }
    .dropzone.dz-drag-hover { background: #eef2ff !important; }
</style>
@endpush
