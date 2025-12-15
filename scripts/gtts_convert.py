#!/usr/bin/env python3
"""
Google Text-to-Speech (gTTS) Converter
Free alternative to Azure TTS

Usage:
    python gtts_convert.py "text" "output.mp3" "vi" "1.0"
    
Arguments:
    text: Text to convert
    output_file: Output MP3 file path
    lang: Language code (vi, en, ja, ko, zh-CN, fr, de, es)
    speed: Speed multiplier (0.5 = slow, 1.0 = normal, 1.5 = fast)
"""

import sys
import os
import json

try:
    from gtts import gTTS
    from pydub import AudioSegment
except ImportError as e:
    print(json.dumps({
        "success": False,
        "error": f"Missing dependency: {str(e)}. Run: pip install gtts pydub"
    }))
    sys.exit(1)

def convert_text_to_speech(text, output_file, lang='vi', speed=1.0):
    """
    Convert text to speech using gTTS
    
    Args:
        text: Text to convert
        output_file: Output file path
        lang: Language code
        speed: Speed multiplier (0.5-2.0)
    
    Returns:
        dict: Result with success status
    """
    try:
        # Validate inputs
        if not text or not text.strip():
            return {"success": False, "error": "Text is empty"}
        
        if len(text) > 10000:
            return {"success": False, "error": "Text too long (max 10000 chars)"}
        
        # Supported languages
        supported_langs = {
            'vi': 'vi',      # Vietnamese
            'en': 'en',      # English
            'ja': 'ja',      # Japanese
            'ko': 'ko',      # Korean
            'zh': 'zh-CN',   # Chinese
            'zh-CN': 'zh-CN',
            'fr': 'fr',      # French
            'de': 'de',      # German
            'es': 'es',      # Spanish
        }
        
        gtts_lang = supported_langs.get(lang, 'vi')
        
        # Create output directory if not exists
        output_dir = os.path.dirname(output_file)
        if output_dir and not os.path.exists(output_dir):
            os.makedirs(output_dir)
        
        # Generate speech with gTTS
        # Note: gTTS doesn't support speed directly, we'll use pydub
        tts = gTTS(text=text, lang=gtts_lang, slow=False)
        
        # Save to temp file first if speed adjustment needed
        if speed != 1.0:
            temp_file = output_file + '.temp.mp3'
            tts.save(temp_file)
            
            # Adjust speed using pydub
            audio = AudioSegment.from_mp3(temp_file)
            
            # Speed up or slow down
            if speed > 1.0:
                # Speed up (max 2x)
                speed = min(speed, 2.0)
                audio = audio.speedup(playback_speed=speed)
            elif speed < 1.0:
                # Slow down (min 0.5x)
                speed = max(speed, 0.5)
                # pydub doesn't have slowdown, use frame rate trick
                new_frame_rate = int(audio.frame_rate * speed)
                audio = audio._spawn(audio.raw_data, overrides={
                    "frame_rate": new_frame_rate
                }).set_frame_rate(audio.frame_rate)
            
            # Export final file
            audio.export(output_file, format='mp3')
            
            # Clean up temp file
            os.remove(temp_file)
        else:
            # Normal speed, save directly
            tts.save(output_file)
        
        # Verify file was created
        if os.path.exists(output_file):
            file_size = os.path.getsize(output_file)
            return {
                "success": True,
                "file_path": output_file,
                "file_size": file_size,
                "lang": gtts_lang,
                "engine": "gtts"
            }
        else:
            return {"success": False, "error": "Failed to create output file"}
            
    except Exception as e:
        return {"success": False, "error": str(e)}


def main():
    """Main entry point"""
    # Check for --base64 flag
    use_base64 = '--base64' in sys.argv
    if use_base64:
        sys.argv.remove('--base64')
    
    if len(sys.argv) < 3:
        print(json.dumps({
            "success": False,
            "error": "Usage: python gtts_convert.py [--base64] 'text' 'output.mp3' [lang] [speed]"
        }))
        sys.exit(1)
    
    text_input = sys.argv[1]
    output_file = sys.argv[2]
    lang = sys.argv[3] if len(sys.argv) > 3 else 'vi'
    speed = float(sys.argv[4]) if len(sys.argv) > 4 else 1.0
    
    # Decode base64 if flag is set
    if use_base64:
        try:
            import base64
            text = base64.b64decode(text_input).decode('utf-8')
        except Exception as e:
            print(json.dumps({
                "success": False,
                "error": f"Failed to decode base64: {str(e)}"
            }))
            sys.exit(1)
    # Check if text is a file reference (starts with @)
    elif text_input.startswith('@'):
        text_file = text_input[1:]
        try:
            with open(text_file, 'r', encoding='utf-8') as f:
                text = f.read()
        except Exception as e:
            print(json.dumps({
                "success": False,
                "error": f"Failed to read text file: {str(e)}"
            }))
            sys.exit(1)
    else:
        text = text_input
    
    result = convert_text_to_speech(text, output_file, lang, speed)
    print(json.dumps(result))
    
    sys.exit(0 if result.get('success') else 1)


if __name__ == '__main__':
    main()
