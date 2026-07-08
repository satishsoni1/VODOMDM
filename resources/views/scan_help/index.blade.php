@extends('layouts.main')

@section('title', 'Scan Help Content')

@section('breadcrumb')
    <li class="breadcrumb-item active">Scan Help Content</li>
@endsection

@section('content')
<p class="text-muted small mb-4">
    Manage the FAQs and help videos shown to anyone who scans a device QR code and lands on its info page.
</p>

<div class="row g-4">
    {{-- ── FAQs ─────────────────────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-question-circle me-1"></i> FAQs</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                    <i class="bi bi-plus-lg"></i> Add FAQ
                </button>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($faqs as $faq)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="me-2">
                                <div class="fw-semibold">{{ $faq->question }}</div>
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($faq->answer, 120) }}</div>
                                @unless($faq->is_active)
                                    <span class="badge bg-secondary mt-1">Inactive</span>
                                @endunless
                            </div>
                            <div class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editFaqModal{{ $faq->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('scan-help.faqs.destroy', $faq) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this FAQ?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>

                    <div class="modal fade" id="editFaqModal{{ $faq->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('scan-help.faqs.update', $faq) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Edit FAQ</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">Question</label>
                                            <input type="text" name="question" class="form-control" value="{{ $faq->question }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">Answer</label>
                                            <textarea name="answer" class="form-control" rows="4" required>{{ $faq->answer }}</textarea>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Sort Order</label>
                                                <input type="number" name="sort_order" class="form-control" value="{{ $faq->sort_order }}">
                                            </div>
                                            <div class="col-6 d-flex align-items-end">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="faqActive{{ $faq->id }}" {{ $faq->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="faqActive{{ $faq->id }}">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <li class="list-group-item text-center text-muted py-4">No FAQs added yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ── Help Videos ─────────────────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-play-circle me-1"></i> Help Videos</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVideoModal">
                    <i class="bi bi-plus-lg"></i> Add Video
                </button>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($videos as $video)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="me-2">
                                <div class="fw-semibold">{{ $video->title }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 320px;">{{ $video->video_url }}</div>
                                @unless($video->is_active)
                                    <span class="badge bg-secondary mt-1">Inactive</span>
                                @endunless
                            </div>
                            <div class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editVideoModal{{ $video->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('scan-help.videos.destroy', $video) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this video?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>

                    <div class="modal fade" id="editVideoModal{{ $video->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('scan-help.videos.update', $video) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Edit Help Video</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $video->title }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">Video URL (YouTube / Vimeo)</label>
                                            <input type="url" name="video_url" class="form-control" value="{{ $video->video_url }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted">Description (optional)</label>
                                            <textarea name="description" class="form-control" rows="2">{{ $video->description }}</textarea>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Sort Order</label>
                                                <input type="number" name="sort_order" class="form-control" value="{{ $video->sort_order }}">
                                            </div>
                                            <div class="col-6 d-flex align-items-end">
                                                <div class="form-check">
                                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="videoActive{{ $video->id }}" {{ $video->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="videoActive{{ $video->id }}">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <li class="list-group-item text-center text-muted py-4">No help videos added yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- ── Add FAQ Modal ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="addFaqModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('scan-help.faqs.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add FAQ</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small text-muted">Question</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Answer</label>
                        <textarea name="answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add FAQ</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Add Video Modal ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="addVideoModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('scan-help.videos.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add Help Video</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small text-muted">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Video URL (YouTube / Vimeo)</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Video</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
