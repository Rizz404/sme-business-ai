        <div id="assistant-app"
            class="stage-chat"
            data-inline="true"
            data-label-connection-error="{{ $stageCopy['connection_error'] }}"
            data-business-slug="{{ $business->slug }}"
            data-locale="{{ $locale }}"
            data-start-url="{{ route('assistant.start', $business->slug) }}"
            data-message-url="{{ route('assistant.message', $business->slug) }}"
            data-history-url="{{ route('assistant.history', $business->slug) }}"
            data-speech-url="{{ route('assistant.speech', $business->slug) }}"
            data-storage-key="assistant_token_{{ $business->slug }}"
            data-label-placeholder="{{ $labels['chat_placeholder'] }}"
            data-label-send="{{ $labels['chat_send'] }}"
            data-label-open="{{ $labels['chat_open'] }}"
            data-label-close="{{ $labels['chat_close'] }}"
            data-label-intro="{{ $labels['chat_intro'] }}"
            data-label-voice-start="{{ $labels['chat_voice_start'] }}"
            data-label-voice-stop="{{ $labels['chat_voice_stop'] }}"
            data-label-voice-listening="{{ $labels['chat_voice_listening'] }}"
            data-label-voice-denied="{{ $labels['chat_voice_denied'] }}"
            data-label-sold-out="{{ $labels['unavailable'] }}"
            data-label-attendees="{{ $labels['attendees'] }}"
            data-label-available-slots="{{ $labels['available_slots'] }}"
            data-label-no-availability="{{ $labels['no_availability'] }}"
            data-label-booking-received="{{ $labels['booking_received'] }}"
            data-label-reference="{{ $labels['reference'] }}"
            data-label-fee-free="{{ $labels['fee_free'] }}"
            data-label-fee="{{ $labels['fee'] }}"
            data-label-thinking="{{ $labels['thinking'] }}"
            data-label-handed-over="{{ $labels['handed_over'] }}"
            data-label-status-sent="{{ $labels['chat_status_sent'] }}"
            data-label-status-waiting="{{ $labels['chat_status_waiting'] }}"
            data-label-status-replied="{{ $labels['chat_status_replied'] }}"
            data-label-chat-expand="{{ $labels['chat_expand'] }}"
            data-label-chat-collapse="{{ $labels['chat_collapse'] }}"
            data-label-draft-title="{{ $labels['chat_draft_title'] }}"
            data-label-draft-body="{{ $labels['chat_draft_body'] }}"
            data-label-draft-keep="{{ $labels['chat_draft_keep'] }}"
            data-label-draft-discard="{{ $labels['chat_draft_discard'] }}"
            data-label-view-details="{{ $labels['view_details'] }}"
            data-label-item-details-question="{{ $labels['item_details_question'] }}"
            data-label-book-slot="{{ $labels['book_slot'] }}"
            data-label-reserve-slot-question="{{ $labels['reserve_slot_question'] }}"
            data-label-reserve-this-slot-question="{{ $labels['reserve_this_slot_question'] }}"
            data-label-reserve-start-question="{{ $labels['reserve_start_question'] }}"
            data-label-booking-pending="{{ $labels['booking_pending'] }}"
            data-label-send-whatsapp="{{ $labels['send_whatsapp'] }}"
            data-label-call-business="{{ $labels['call_business'] }}"
            data-label-email-business="{{ $labels['email_business'] }}"
            data-terms="{{ json_encode(['tag' => $terms['tag'], 'requirement' => $terms['requirement'], 'area_type' => $terms['area_type'], 'feature' => $terms['feature'], 'delivery' => $terms['delivery']], JSON_UNESCAPED_UNICODE) }}"
            data-label-catalog-heading="{{ $labels['catalog_heading_chat'] }}"
            data-label-staff="{{ $labels['team'] }}"
            data-label-error="{{ $labels['chat_error'] }}"
            data-label-slow="{{ $labels['chat_slow'] }}"
            data-label-retry="{{ $labels['chat_retry'] }}"
            data-currency="{{ $business->currency }}"
            data-home-url="{{ route('business.show', ['businessSlug' => $business->slug, 'lang' => $locale]) }}"
            data-item-url="{{ route('business.catalog-item', ['businessSlug' => $business->slug, 'itemSlug' => '__SLUG__', 'lang' => $locale]) }}"
            data-staff-url="{{ route('business.staff', ['businessSlug' => $business->slug, 'lang' => $locale]) }}"
        >
            <section id="assistant-chat-log" class="chat-log" aria-label="{{ $labels['chat_heading'] }}" aria-hidden="true">
                <header class="chat-log-header">
                    <span class="chat-host-avatar" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-1.5 font-semibold text-stone-900">
                            {{ $labels['chat_heading'] }}
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        </p>
                        <p class="truncate text-xs text-stone-500">{{ $labels['chat_subtitle'] }}</p>
                        <p data-chat-status class="chat-status" aria-live="polite" hidden></p>
                    </div>
                    <button type="button" data-chat-new class="chat-log-new" aria-label="{{ $labels['chat_new'] }}">{{ $labels['chat_new'] }}</button>
                    <button type="button" data-chat-expand class="chat-log-expand" aria-pressed="false" aria-label="{{ $labels['chat_expand'] }}" title="{{ $labels['chat_expand'] }}"><svg class="chat-expand-icon-wide" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4H4v5M15 4h5v5M9 20H4v-5M15 20h5v-5"/></svg><svg class="chat-expand-icon-narrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9h5V4M20 9h-5V4M4 15h5v5M20 15h-5v5"/></svg></button>
                    <button type="button" data-chat-close class="chat-log-close" aria-label="{{ $labels['chat_close'] }}"><span aria-hidden="true">×</span></button>
                </header>

                <div data-messages role="log" aria-live="polite" aria-label="{{ $labels['chat_heading'] }}" class="chat-log-messages space-y-3"></div>

                <div data-thinking-indicator class="chat-thinking" role="status" hidden>
                    <span class="chat-host-avatar" aria-hidden="true"></span>
                    <span class="chat-thinking-bubble">
                        <span class="chat-thinking-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span>{{ $labels['thinking'] }}</span>
                    </span>
                </div>

                <div data-status-banner class="hidden border-t border-rose-200 bg-rose-50 px-4 py-2 text-xs text-rose-800"></div>
            </section>

            <form data-chat-form data-chat-composer class="chat-bar" aria-hidden="true">
                <input
                    type="text"
                    data-chat-input
                    aria-label="{{ $labels['chat_placeholder'] }}"
                    maxlength="4000"
                    placeholder="{{ $labels['chat_placeholder'] }}"
                    autocomplete="off"
                >
                <button type="button" data-chat-mic class="chat-mic" aria-pressed="false" aria-label="{{ $labels['chat_voice_start'] }}" title="{{ $labels['chat_voice_start'] }}" hidden>
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="3" width="6" height="12" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/></svg>
                </button>
                <button type="submit" data-chat-submit>{{ $labels['chat_send'] }}</button>
            </form>

            <div data-draft-dialog class="chat-draft-dialog" hidden aria-hidden="true" role="dialog" aria-modal="false" aria-labelledby="chat-draft-title">
                <p id="chat-draft-title" data-draft-title class="font-semibold text-stone-900">{{ $labels['chat_draft_title'] }}</p>
                <p data-draft-body class="mt-1 text-xs leading-relaxed text-stone-600">{{ $labels['chat_draft_body'] }}</p>
                <div class="mt-3 flex justify-end gap-2">
                    <button type="button" data-draft-keep class="rounded-full border border-stone-300 bg-white px-3 py-1.5 text-xs font-medium text-stone-700">{{ $labels['chat_draft_keep'] }}</button>
                    <button type="button" data-draft-discard class="rounded-full bg-stone-900 px-3 py-1.5 text-xs font-medium text-white">{{ $labels['chat_draft_discard'] }}</button>
                </div>
            </div>

            <button type="button" data-chat-launcher class="chat-launcher" aria-controls="assistant-chat-log" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 5h14v10h-8l-4 4v-4H5z"/><path d="M9 9.5h6M9 12h3.5"/></svg>
                <span class="chat-launcher-dot" aria-hidden="true"></span>
                <span class="chat-launcher-label">{{ $labels['chat_open'] }}</span>
            </button>
        </div>
