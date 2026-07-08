@if($faqs->isNotEmpty() || $videos->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><i class="bi bi-question-circle me-1"></i> Need Help?</div>
    <div class="card-body">
        @if($videos->isNotEmpty())
            <div class="mb-3">
                @foreach($videos as $video)
                    <div class="mb-3">
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ $video->embed_url }}" title="{{ $video->title }}" allowfullscreen loading="lazy"></iframe>
                        </div>
                        <div class="fw-semibold small mt-1">{{ $video->title }}</div>
                        @if($video->description)
                            <div class="text-muted small">{{ $video->description }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($faqs->isNotEmpty())
            <div class="accordion" id="faqAccordion">
                @foreach($faqs as $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $faq->id }}">
                                {{ $faq->question }}
                            </button>
                        </h2>
                        <div id="faq{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-muted">{!! nl2br(e($faq->answer)) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif
