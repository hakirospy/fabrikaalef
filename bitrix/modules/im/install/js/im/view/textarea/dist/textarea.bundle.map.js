{"version":3,"sources":["textarea.bundle.js"],"names":["exports","ui_vue","im_lib_localstorage","im_lib_utils","Vue","component","props","siteId","default","userId","dialogId","enableCommand","enableMention","desktopMode","enableEdit","enableFile","sendByEnter","autoFocus","writesEventLetter","styles","type","Object","_default","listenEventInsertText","listenEventFocus","listenEventBlur","data","placeholderMessage","currentMessage","previousMessage","commandListen","mentionListen","stylesDefault","freeze","button","backgroundColor","iconColor","created","this","event","$on","onInsertText","$root","onFocusSet","onFocusClear","localStorage","LocalStorage","textareaHistory","get","beforeDestroy","$off","clearTimeout","messageStoreTimeout","set","computed","textareaClassName","Utils","device","isMobile","buttonStyle","assign","isIconDark","isDarkColor","className","style","localize","getFilteredPhrases","$bitrixMessages","directives","bx-im-focus","inserted","element","params","value","focus","methods","insertText","text","breakline","arguments","length","undefined","position","cursor","textarea","$refs","selectionStart","selectionEnd","substring","trim","endsWith","textChangeEvent","sendMessage","$emit","_this","previousSelectionStart","previousSelectionEnd","toString","setTimeout","onKeyDown","target","isMac","platform","isCtrlTEnable","isBitrixDesktop","browser","isChrome","altKey","ctrlKey","shiftKey","keyCode","document","activeElement","preventDefault","stopPropagation","metaKey","key","includes","tagStart","toLowerCase","tagEnd","selected","startsWith","indexOf","onKeyUp","onPaste","$nextTick","onInput","onFocus","onBlur","onAppButtonClick","appId","blur","onFileClick","onFileSelect","fileInput","template","window","BX","Messenger","Lib"],"mappings":"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","file":"textarea.bundle.map.js"}