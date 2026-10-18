<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>

 <!--Tools-->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/link@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>
 <!--Image Tool-->
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>


<script>

      const pendingImages = new Map();
      const editor = new EditorJS({
        holder: 'editorjs',
        placeholder: 'Start writing your article...',
        tools: {
            header: {
            class: Header,
            inlineToolbar: true,
            config: {
              levels: [1, 2, 3, 4, 5, 6],
              defaultLevel: 2
            }
            },
            list: {
            class: EditorjsList,
            inlineToolbar: true,
            config: {
              defaultStyle: 'unordered'
            }
            },
            quote: {
            class: Quote,
            inlineToolbar: true
            },
            embed: {
            class: Embed,
            config: {
              services: {
                youtube: true,
                twitter: true,
                instagram: true,
                vimeo: true
              }
            }
            },
            marker: Marker,          // Highlight text
            inlineCode: InlineCode,  // Inline code snippets
            linkTool: {
            class: LinkTool,
            config: {
              endpoint: '/fetchUrl', // Your backend endpoint to fetch link metadata
            }
            },
            code: CodeTool,
            table: Table,
            image: {
                class: ImageTool,
                config: {
                    uploader: {
                        // Intercept the file selection before any network request happens
                        uploadByFile(file) {
                            
                            // Allow only images
                            if (!file.type.startsWith("image/")) {
                                return Promise.reject("Only image files are allowed.");
                            }
                            
                            // Generate a unique temporary ID
                            const tempId = 'img_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
                            const previewUrl = URL.createObjectURL(file);
                            
                            // Store the actual File object in our Map
                            pendingImages.set(tempId, {file, previewUrl});
                            
                            // RETURN A NEW PROMISE WITH A TIMEOUT (This fixes the spinner bug)
                            return new Promise((resolve) => {
                                setTimeout(() => {
                                    resolve({
                                        success: 1,
                                        file: {
                                            url: previewUrl, // Creates a fast local preview
                                            tempId: tempId // We inject this custom property to track it later
                                        }
                                    });
                                }, 250); // 250ms is just enough time for the Editor.js UI to catch up
                            });
                            
                        }
                    }
                }
            }
        }
    });
    
    
    
    
// const customParsers = {
//     // 1. Paragraphs
//     paragraph: (block) => {
//         return `<p class="mb-6 text-muted dark:text-gray-600 text-lg leading-relaxed">
//                     ${block.data.text}
//                 </p>`;
//     },

//     // 2. Headers (Dynamic sizing based on level)
//     header: (block) => {
//         const sizes = {
//             1: 'text-4xl',
//             2: 'text-3xl',
//             3: 'text-2xl',
//             4: 'text-xl',
//             5: 'text-lg',
//             6: 'text-base'
//         };
//         const sizeClass = sizes[block.data.level] || 'text-2xl';
        
//         return `<h${block.data.level} class="font-serif ${sizeClass} font-bold mt-10 mb-5 text-foreground transition-colors">
//                     ${block.data.text}
//                 </h${block.data.level}>`;
//     },

    // 3. Lists (Handles both unordered and ordered)
//     list: (block) => {
//         const tag = block.data.style === 'ordered' ? 'ol' : 'ul';
//         const listStyle = block.data.style === 'ordered' ? 'list-decimal' : 'list-disc';
        
//         const items = block.data.items.map(item => `<li class="mb-2">${item.content}</li>`).join('');
//         return `<${tag} class="${listStyle} list-outside ml-6 mb-6 text-muted dark:text-gray-400 leading-relaxed text-lg">
//                     ${items}
//                 </${tag}>`;
//     },

//     // 4. Images (With optional captions)
//     image: (block) => {
//         const caption = block.data.caption 
//             ? `<figcaption class="text-center text-sm text-muted mt-3 italic">${block.data.caption}</figcaption>` 
//             : '';
            
//         return `<figure class="my-10 w-full">
//                     <img src="${block.data.file.url}" alt="${block.data.caption || 'Blog graphic'}" class="w-full rounded-2xl object-cover shadow-md" loading="lazy" />
//                     ${caption}
//                 </figure>`;
//     },

//     // 5. Quotes
//     quote: (block) => {
//         const author = block.data.caption 
//             ? `<cite class="block mt-3 text-sm font-semibold text-accent not-italic">— ${block.data.caption}</cite>` 
//             : '';

//         return `<blockquote class="border-l-4 border-accent pl-3 my-6 bg-surface p-1 rounded-r-2xl shadow-sm">
//                     <p class="text-xl italic font-serif text-foreground leading-snug">"${block.data.text}"</p>
//                     ${author}
//                 </blockquote>`;
//     }
// };

// // const editorParser = edjsHTML(customParsers);

</script>