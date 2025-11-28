<div class="space-y-4">
    @forelse ($emails as $email)
        <div
            x-data="{ expanded: false }"
            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        @if ($email->status === 'sent')
                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30">
                                <x-heroicon-o-check-circle class="w-3 h-3" />
                                Sent
                            </span>
                        @else
                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 bg-danger-50 text-danger-600 ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/30">
                                <x-heroicon-o-x-circle class="w-3 h-3" />
                                Failed
                            </span>
                        @endif
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ ($email->sent_at ?? $email->created_at)->timezone(config('app.timezone'))->format('M j, Y \a\t H:i') }}
                        </span>
                    </div>

                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $email->subject }}
                    </p>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        To: {{ $email->to_email }}
                        @if ($email->sentBy)
                            &middot; By: {{ $email->sentBy->name }}
                        @endif
                    </p>
                </div>

                <button
                    type="button"
                    x-on:click="expanded = !expanded"
                    class="flex-shrink-0 inline-flex items-center justify-center gap-1 rounded-lg border border-gray-300 dark:border-gray-600 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                    <span x-text="expanded ? 'Hide' : 'View'">View</span>
                </button>
            </div>

            {{-- Inline expandable section - no overlay, no global listeners --}}
            <div
                x-show="expanded"
                x-collapse
                x-cloak
                class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
            >
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subject</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $email->subject }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">To</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $email->to_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Body</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap bg-gray-50 dark:bg-gray-900 rounded-lg p-3 max-h-64 overflow-y-auto">{{ $email->body }}</dd>
                    </div>
                    @if (!empty($email->attachments))
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Attachments</dt>
                            <dd class="mt-1">
                                <ul class="space-y-1">
                                    @foreach ($email->attachments as $attachment)
                                        <li>
                                            <a
                                                href="{{ route('admin.attachment.download', ['path' => urlencode($attachment)]) }}"
                                                class="inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
                                                target="_blank"
                                            >
                                                <x-heroicon-o-paper-clip class="w-4 h-4" />
                                                {{ basename($attachment) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    @empty
        <div class="text-center py-6">
            <x-heroicon-o-envelope class="w-12 h-12 mx-auto text-gray-400" />
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No emails sent yet</p>
        </div>
    @endforelse
</div>
