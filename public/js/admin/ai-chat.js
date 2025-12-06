document.addEventListener("DOMContentLoaded", () => {
    // ========================================
    // AI Chat Widget Logic
    // ========================================
    const chatWidget = document.getElementById("ai-chat-widget");
    if (!chatWidget) return;

    const context = chatWidget.getAttribute("data-context") || "general";
    const chatToggle = document.getElementById("ai-chat-toggle");
    const chatBox = document.getElementById("ai-chat-box");
    const chatClose = document.getElementById("ai-chat-close");
    const chatMaximize = document.getElementById("ai-chat-maximize");
    const maximizeIcon = document.getElementById("maximize-icon");
    const chatBackdrop = document.getElementById("ai-chat-backdrop");
    const chatForm = document.getElementById("ai-chat-form");
    const chatInput = document.getElementById("ai-chat-input");
    const chatMessages = document.getElementById("ai-chat-messages");

    let isMaximized = false;

    if (chatToggle && chatBox) {
        // Toggle Chat Box Open/Close
        const toggleChat = () => {
            const isHidden = chatBox.classList.contains("d-none");
            chatBox.classList.toggle("d-none");
            if (isHidden) {
                chatToggle.classList.add("active");
                chatInput.focus();
            } else {
                chatToggle.classList.remove("active");
                // Close also minimizes if maximized
                if (isMaximized) {
                    minimizeChat();
                }
            }
        };

        // Maximize/Minimize Chat
        const toggleMaximize = () => {
            isMaximized = !isMaximized;
            if (isMaximized) {
                chatBox.classList.add("maximized");
                chatBackdrop.classList.add("active");
                maximizeIcon.className = "bi bi-arrows-angle-contract";
            } else {
                chatBox.classList.remove("maximized");
                chatBackdrop.classList.remove("active");
                maximizeIcon.className = "bi bi-arrows-fullscreen";
            }
        };

        const minimizeChat = () => {
            if (isMaximized) {
                isMaximized = false;
                chatBox.classList.remove("maximized");
                chatBackdrop.classList.remove("active");
                maximizeIcon.className = "bi bi-arrows-fullscreen";
            }
        };

        // Event Listeners
        chatToggle.addEventListener("click", toggleChat);
        chatClose.addEventListener("click", toggleChat);
        chatMaximize.addEventListener("click", toggleMaximize);
        chatBackdrop.addEventListener("click", minimizeChat);

        // Helper: Get current time
        const getCurrentTime = () => {
            const now = new Date();
            return now.toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
            });
        };

        // Helper: Format markdown to HTML for better display
        const formatMarkdown = (text) => {
            if (!text) return text;

            // Convert newlines to <br>
            text = text.replace(/\n/g, "<br>");

            // Convert numbered lists (1. 2. 3.)
            text = text.replace(
                /^(\d+)\.\s+(.+)$/gm,
                '<div class="numbered-item"><strong>$1.</strong> $2</div>'
            );

            // Convert bullet points (•, -, *)
            text = text.replace(
                /^[•\-\*]\s+(.+)$/gm,
                '<div class="bullet-item">• $1</div>'
            );

            // Convert bold (**text**)
            text = text.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");

            // Convert code (`code`)
            text = text.replace(/`(.+?)`/g, "<code>$1</code>");

            return text;
        };

        // Helper: Add Message to UI (WhatsApp Style)
        const addMessage = (text, sender, sources = []) => {
            const isUser = sender === "user";
            const bubbleClass = isUser ? "chat-bubble-user" : "chat-bubble-ai";
            const senderName = isUser ? "Anda" : "AI Assistant";
            const senderIcon = isUser ? "person-circle" : "robot";

            // Format AI responses with markdown
            const formattedText = sender === "ai" ? formatMarkdown(text) : text;

            const messageDiv = document.createElement("div");
            messageDiv.className = `chat-bubble ${bubbleClass}`;
            messageDiv.innerHTML = `
                        <div class="chat-bubble-meta">
                            <i class="bi bi-${senderIcon}"></i>
                            <span>${senderName}</span>
                        </div>
                        <div class="chat-bubble-text">${formattedText}</div>
                        <div class="chat-bubble-time">${getCurrentTime()}</div>
                    `;

            chatMessages.appendChild(messageDiv);

            // Auto-scroll to bottom with smooth animation
            setTimeout(() => {
                chatMessages.scrollTo({
                    top: chatMessages.scrollHeight,
                    behavior: "smooth",
                });
            }, 100);
        };

        // Helper: Show "Thinking" Process Indicator
        const showThinkingIndicator = (userMessage) => {
            const typingId = "typing-indicator";
            const typingDiv = document.createElement("div");
            typingDiv.id = typingId;
            typingDiv.style.opacity = "0.7";
            typingDiv.style.fontSize = "0.85rem";
            typingDiv.className =
                "chat-bubble chat-bubble-ai d-flex align-items-center gap-2";

            // Determine context for "Thinking Steps"
            let steps = ["⏳ Sedang berpikir..."];
            const lowerMsg = userMessage.toLowerCase();

            if (
                lowerMsg.includes("stok") ||
                lowerMsg.includes("sisa") ||
                lowerMsg.includes("barang")
            ) {
                steps = [
                    "🔍 Menganalisis pertanyaan...",
                    "📦 Mengecek database inventaris...",
                    "🔢 Menghitung stok tersedia...",
                    "✨ Menyusun jawaban...",
                ];
            } else if (
                lowerMsg.includes("jual") ||
                lowerMsg.includes("omset") ||
                lowerMsg.includes("laku")
            ) {
                steps = [
                    "🔍 Menganalisis pertanyaan...",
                    "💰 Mengakses data transaksi...",
                    "📊 Mengkalkulasi total penjualan...",
                    "📈 Menganalisa tren...",
                    "✨ Menyusun laporan...",
                ];
            } else if (
                lowerMsg.includes("pelanggan") ||
                lowerMsg.includes("beli")
            ) {
                steps = [
                    "🔍 Menganalisis pertanyaan...",
                    "👥 Mencari data pelanggan...",
                    "⭐ Mengecek riwayat pembelian...",
                    "✨ Menyusun informasi...",
                ];
            } else {
                steps = [
                    "🧠 Memproses input...",
                    "📡 Menghubungi AI Brain...",
                    "✨ Menyusun respon...",
                ];
            }

            typingDiv.innerHTML = `
                <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
                <span id="thinking-text" class="text-secondary fst-italic typewriter">${steps[0]}</span>
            `;

            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Cycle through steps
            let stepIndex = 0;
            const intervalId = setInterval(() => {
                stepIndex = (stepIndex + 1) % steps.length;
                const textEl = document.getElementById("thinking-text");
                if (textEl) {
                    textEl.innerText = steps[stepIndex];
                }
            }, 1500); // Change text every 1.5s

            return { id: typingId, interval: intervalId };
        };

        // Handle Submit
        chatForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            // Display user message
            addMessage(message, "user");
            chatInput.value = "";
            chatInput.disabled = true;

            // Show thinking indicator with context
            const thinkingData = showThinkingIndicator(message);

            try {
                // Send to server
                const response = await fetch("/superadmin/ai/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        message: message,
                        context: context, // Send context to server
                    }),
                });

                const data = await response.json();

                // Remove typing indicator & clear interval
                clearInterval(thinkingData.interval);
                document.getElementById(thinkingData.id)?.remove();

                // Display AI reply with sources
                addMessage(data.reply, "ai", data.sources);
            } catch (error) {
                console.error("Chat Error:", error);

                // Cleanup
                clearInterval(thinkingData.interval);
                document.getElementById(thinkingData.id)?.remove();

                addMessage(
                    "❌ Maaf, terjadi kesalahan koneksi. Silakan coba lagi.",
                    "ai"
                );
            } finally {
                chatInput.disabled = false;
                chatInput.focus();
            }
        });
    }
});
