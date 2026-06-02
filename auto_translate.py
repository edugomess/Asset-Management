import json
import time
from deep_translator import GoogleTranslator

# Load the missing keys
with open('missing.json', 'r', encoding='utf-8') as f:
    missing_keys = json.load(f)

translator = GoogleTranslator(source='pt', target='en')
translated_dict = {}

print(f"Translating {len(missing_keys)} items...")

count = 0
for key in missing_keys:
    try:
        if not key.strip():
            translated_dict[key] = key
            continue
            
        # simple caching/avoiding translation of numbers
        if key.isdigit() or key.lower() in ['pdf', 'id', 'mac', 'ip', 'sim']:
            translated_dict[key] = key
        else:
            translated_dict[key] = translator.translate(key)
        
        count += 1
        if count % 50 == 0:
            print(f"Translated {count}/{len(missing_keys)}")
            time.sleep(1) # sleep briefly to avoid rate limiting
    except Exception as e:
        print(f"Error translating '{key}': {e}")
        translated_dict[key] = key # fallback to original

with open('missing_translated.json', 'w', encoding='utf-8') as f:
    json.dump(translated_dict, f, ensure_ascii=False, indent=4)

print("Translation complete. Saved to missing_translated.json")
