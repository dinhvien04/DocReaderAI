#!/usr/bin/env python3
"""
Edge Text-to-Speech Converter
Uses Microsoft Edge's TTS engine (free, high quality)

Usage:
    python edge_tts_convert.py "text" "output.mp3" "vi-VN-HoaiMyNeural" "1.0"
    
Arguments:
    text: Text to convert
    output_file: Output MP3 file path
    voice: Voice name (e.g., vi-VN-HoaiMyNeural)
    rate: Speed rate (+0% = normal, +50% = faster, -50% = slower)
"""

import sys
import os
import json
import asyncio
import base64

try:
    import edge_tts
except ImportError as e:
    print(json.dumps({
        "success": False,
        "error": f"Missing dependency: {str(e)}. Run: pip install edge-tts"
    }))
    sys.exit(1)


async def convert_text_to_speech(text, output_file, voice='vi-VN-HoaiMyNeural', rate='+0%'):
    """
    Convert text to speech using Edge TTS
    
    Args:
        text: Text to convert
        output_file: Output file path
        voice: Voice name
        rate: Speed rate (e.g., '+0%', '+50%', '-50%')
    
    Returns:
        dict: Result with success status
    """
    try:
        # Validate inputs
        if not text or not text.strip():
            return {"success": False, "error": "Text is empty"}
        
        if len(text) > 15000:
            return {"success": False, "error": "Text too long (max 15000 chars)"}
        
        # Create output directory if not exists
        output_dir = os.path.dirname(output_file)
        if output_dir and not os.path.exists(output_dir):
            os.makedirs(output_dir, exist_ok=True)
        
        # Normalize rate format (ensure it's valid)
        if not rate or rate == '1.0' or rate == '1':
            rate = '+0%'
        
        # Create TTS communicator with error handling
        try:
            communicate = edge_tts.Communicate(text, voice, rate=rate)
        except Exception as comm_error:
            return {"success": False, "error": f"Failed to create communicator: {str(comm_error)}"}
        
        # Save to file with timeout
        try:
            await asyncio.wait_for(communicate.save(output_file), timeout=60.0)
        except asyncio.TimeoutError:
            return {"success": False, "error": "Conversion timeout (60s)"}
        except Exception as save_error:
            return {"success": False, "error": f"Failed to save audio: {str(save_error)}"}
        
        # Verify file was created and has content
        if os.path.exists(output_file):
            file_size = os.path.getsize(output_file)
            if file_size > 0:
                return {
                    "success": True,
                    "file_path": output_file,
                    "file_size": file_size,
                    "voice": voice,
                    "rate": rate,
                    "engine": "edge-tts"
                }
            else:
                return {"success": False, "error": "Output file is empty"}
        else:
            return {"success": False, "error": "Failed to create output file"}
            
    except Exception as e:
        import traceback
        return {"success": False, "error": f"{str(e)}\n{traceback.format_exc()}"}


def speed_to_rate(speed):
    """
    Convert speed multiplier to Edge TTS rate format
    
    Args:
        speed: Speed multiplier (0.5 = slow, 1.0 = normal, 2.0 = fast)
    
    Returns:
        str: Rate string (e.g., '+0%', '+25%', '-25%')
    """
    try:
        speed_float = float(speed)
    except (ValueError, TypeError):
        return '+0%'
    
    if speed_float == 1.0:
        return '+0%'
    elif speed_float > 1.0:
        # Speed up: 1.25 -> +25%, 1.5 -> +50%
        # Edge TTS max is around +100%
        percent = min(int((speed_float - 1.0) * 100), 100)
        return f'+{percent}%'
    else:
        # Slow down: 0.75 -> -25%, 0.5 -> -50%
        # Edge TTS min is around -50%
        percent = max(int((1.0 - speed_float) * 100), 50)
        return f'-{percent}%'


def main():
    """Main entry point"""
    # Check for --base64 flag
    use_base64 = '--base64' in sys.argv
    if use_base64:
        sys.argv.remove('--base64')
    
    if len(sys.argv) < 3:
        print(json.dumps({
            "success": False,
            "error": "Usage: python edge_tts_convert.py [--base64] 'text' 'output.mp3' [voice] [speed]"
        }))
        sys.exit(1)
    
    text_input = sys.argv[1]
    output_file = sys.argv[2]
    voice = sys.argv[3] if len(sys.argv) > 3 else 'vi-VN-HoaiMyNeural'
    
    # Decode base64 if flag is set
    if use_base64:
        try:
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
    
    # Convert speed to rate
    if len(sys.argv) > 4:
        speed = float(sys.argv[4])
        rate = speed_to_rate(speed)
    else:
        rate = '+0%'
    
    # Run async function
    result = asyncio.run(convert_text_to_speech(text, output_file, voice, rate))
    print(json.dumps(result))
    
    sys.exit(0 if result.get('success') else 1)


if __name__ == '__main__':
    main()
