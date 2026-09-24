<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    @php
    $length = $getLength();
    $statePath = $getStatePath();
    $isAutofocused = $isAutofocused();
    $isDisabled = $isDisabled();
    $entangleExpression = $applyStateBindingModifiers("\$entangle('{$statePath}')");
    @endphp

    <style>
        .otp-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 4px 0 12px 0;
        }

        .otp-note-top {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            margin-bottom: 20px;
            letter-spacing: -0.01em;
        }

        :is(.dark) .otp-note-top {
            color: #f1f5f9;
        }

        .otp-boxes-row {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            margin: 0 auto !important;
        }

        @media (max-width: 480px) {
            .otp-boxes-row {
                gap: 6px !important;
            }
        }

        .otp-box-cell {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .otp-input-field {
            display: block !important;
            width: 56px !important;
            height: 64px !important;
            border-radius: 16px !important;
            border: 2px solid #d1d5db !important;
            background-color: #f9fafb !important;
            text-align: center !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-size: 26px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            outline: none !important;
            box-shadow: none !important;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease !important;
            -webkit-appearance: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        @media (max-width: 480px) {
            .otp-input-field {
                width: 44px !important;
                height: 52px !important;
                border-radius: 12px !important;
                font-size: 20px !important;
            }
        }

        :is(.dark) .otp-input-field {
            background-color: rgba(39, 39, 42, 0.5) !important;
            border-color: #3f3f46 !important;
            color: #ffffff !important;
        }

        /* Focused box style - vibrant blue border matching user screenshot */
        .otp-input-field.is-focused,
        .otp-input-field:focus {
            border-color: #3b82f6 !important;
            background-color: #ffffff !important;
        }

        :is(.dark) .otp-input-field.is-focused,
        :is(.dark) .otp-input-field:focus {
            border-color: #3b82f6 !important;
            background-color: #18181b !important;
        }

        /* Filled box style */
        .otp-input-field.is-filled {
            border-color: #9ca3af !important;
            background-color: #ffffff !important;
        }

        :is(.dark) .otp-input-field.is-filled {
            border-color: #52525b !important;
            background-color: #18181b !important;
        }

        /* Error state */
        .otp-input-field.has-error {
            border-color: #ef4444 !important;
        }

        /* Horizontal Dash placeholder */
        .otp-dash-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 2.5px;
            background-color: #4b5563;
            border-radius: 9999px;
            pointer-events: none;
            transition: opacity 0.1s ease;
        }

        @media (max-width: 480px) {
            .otp-dash-placeholder {
                width: 12px;
                height: 2px;
            }
        }

        :is(.dark) .otp-dash-placeholder {
            background-color: #9ca3af;
        }

        .otp-note-bottom {
            font-size: 13.5px;
            font-weight: 500;
            color: #64748b;
            text-align: center;
            margin-top: 22px;
            line-height: 1.5;
            max-width: 400px;
        }

        :is(.dark) .otp-note-bottom {
            color: #94a3b8;
        }
    </style>

    <div
        x-data="{
            state: $wire.{{ $entangleExpression }},
            digits: Array({{ $length }}).fill(''),
            length: {{ $length }},
            focusedIndex: null,

            init() {
                if (this.state) {
                    this.syncFromState(this.state);
                }

                this.$watch('state', (newVal) => {
                    if ((newVal ?? '') !== this.getJoined()) {
                        this.syncFromState(newVal);
                    }
                });

                @if($isAutofocused)
                    this.$nextTick(() => {
                        this.focusInput(0);
                    });
                @endif
            },

            syncFromState(val) {
                const clean = (val ?? '').toString().replace(/\D/g, '').slice(0, this.length);
                for (let i = 0; i < this.length; i++) {
                    this.digits[i] = clean[i] || '';
                    if (this.$refs['otp_' + i]) {
                        this.$refs['otp_' + i].value = this.digits[i];
                    }
                }
            },

            getJoined() {
                return this.digits.join('');
            },

            updateState() {
                const joined = this.getJoined();
                this.state = joined.length === 0 ? null : joined;
                if (this.$refs.hiddenInput) {
                    this.$refs.hiddenInput.value = this.state ?? '';
                    this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            },

            focusInput(index) {
                const target = Math.max(0, Math.min(index, this.length - 1));
                const el = this.$refs['otp_' + target];
                if (el) {
                    el.focus();
                    el.select();
                }
            },

            handleKeyDown(e, index) {
                // Navigation and helper keys
                if (['Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'Enter'].includes(e.key)) {
                    if (e.key === 'ArrowLeft' && index > 0) {
                        e.preventDefault();
                        this.focusInput(index - 1);
                    } else if (e.key === 'ArrowRight' && index < this.length - 1) {
                        e.preventDefault();
                        this.focusInput(index + 1);
                    } else if (e.key === 'Home') {
                        e.preventDefault();
                        this.focusInput(0);
                    } else if (e.key === 'End') {
                        e.preventDefault();
                        this.focusInput(this.length - 1);
                    }
                    return;
                }

                // Allow modifier shortcuts (Ctrl+C, Ctrl+V, Cmd+A, etc.)
                if (e.ctrlKey || e.metaKey || e.altKey) {
                    return;
                }

                // Backspace navigation
                if (e.key === 'Backspace') {
                    e.preventDefault();
                    if (this.digits[index] !== '') {
                        this.digits[index] = '';
                        if (this.$refs['otp_' + index]) {
                            this.$refs['otp_' + index].value = '';
                        }
                        this.updateState();
                    } else if (index > 0) {
                        this.digits[index - 1] = '';
                        if (this.$refs['otp_' + (index - 1)]) {
                            this.$refs['otp_' + (index - 1)].value = '';
                        }
                        this.updateState();
                        this.focusInput(index - 1);
                    }
                    return;
                }

                // Delete key
                if (e.key === 'Delete') {
                    e.preventDefault();
                    this.digits[index] = '';
                    if (this.$refs['otp_' + index]) {
                        this.$refs['otp_' + index].value = '';
                    }
                    this.updateState();
                    return;
                }

                // STRICTLY ACCEPT ONLY NUMBERS (0-9)
                if (!/^[0-9]$/.test(e.key)) {
                    e.preventDefault();
                    return;
                }
            },

            handleInput(e, index) {
                e.stopPropagation();
                let val = (e.target.value || '').replace(/\D/g, '');

                if (!val) {
                    this.digits[index] = '';
                    e.target.value = '';
                    this.updateState();
                    return;
                }

                if (val.length > 1) {
                    this.distribute(val, index);
                    return;
                }

                const digit = val.slice(-1);
                this.digits[index] = digit;
                e.target.value = digit;
                this.updateState();

                if (index < this.length - 1) {
                    this.focusInput(index + 1);
                }
            },

            handlePaste(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                const clean = pasted.replace(/\D/g, '').slice(0, this.length);
                if (!clean) return;

                for (let i = 0; i < this.length; i++) {
                    this.digits[i] = clean[i] || '';
                    if (this.$refs['otp_' + i]) {
                        this.$refs['otp_' + i].value = this.digits[i];
                    }
                }
                this.updateState();

                const nextFocus = Math.min(clean.length, this.length - 1);
                this.focusInput(nextFocus);
            },

            distribute(val, startIndex = 0) {
                const clean = val.replace(/\D/g, '');
                for (let i = 0; i < clean.length && (startIndex + i) < this.length; i++) {
                    const idx = startIndex + i;
                    this.digits[idx] = clean[i];
                    if (this.$refs['otp_' + idx]) {
                        this.$refs['otp_' + idx].value = clean[i];
                    }
                }
                this.updateState();
                const nextIdx = Math.min(startIndex + clean.length, this.length - 1);
                this.focusInput(nextIdx);
            },

            handleFocus(e, index) {
                this.focusedIndex = index;
                e.target.select();
            },

            handleBlur(e, index) {
                if (this.focusedIndex === index) {
                    this.focusedIndex = null;
                }
            }
        }"
        class="otp-wrapper">
        {{-- Hidden input for form serialization --}}
        <input
            type="hidden"
            x-ref="hiddenInput"
            name="{{ $statePath }}"
            :value="state" />

        {{-- Note directly above boxes --}}
        <div class="otp-note-top">
            Be carful not to share the code with anyone.
        </div>

        {{-- 6 Rounded Boxes Container --}}
        <div
            role="group"
            aria-label="6-digit authentication code"
            class="otp-boxes-row"
            @paste="handlePaste($event)"
            @drop.prevent>
            @for ($i = 0; $i < $length; $i++)
                <div class="otp-box-cell">
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                    x-ref="otp_{{ $i }}"
                    x-bind:value="digits[{{ $i }}] || ''"
                    @input="handleInput($event, {{ $i }})"
                    @keydown="handleKeyDown($event, {{ $i }})"
                    @focus="handleFocus($event, {{ $i }})"
                    @blur="handleBlur($event, {{ $i }})"
                    @click="handleFocus($event, {{ $i }})"
                    class="otp-input-field"
                    :class="{
                            'is-focused': focusedIndex === {{ $i }},
                            'is-filled': focusedIndex !== {{ $i }} && digits[{{ $i }}] !== ''
                        }"
                    {{ $isDisabled ? 'disabled' : '' }} />

                {{-- Horizontal Dash Placeholder when box is empty --}}
                <span
                    x-show="digits[{{ $i }}] === ''"
                    class="otp-dash-placeholder"></span>
        </div>
        @endfor
    </div>

    {{-- Note directly below boxes --}}
    <div class="otp-note-bottom">
        <!-- Did not receive the email? Check your spam filter, or try another email address -->
    </div>
    </div>
</x-dynamic-component>