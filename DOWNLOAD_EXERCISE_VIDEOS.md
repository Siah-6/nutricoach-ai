# How to Download Exercise Videos from ExRx.net

## Method 1: Using Browser DevTools (Easiest)

1. **Open Chrome/Edge**
2. **Press F12** to open DevTools
3. **Go to Network tab**
4. **Click on "Media" filter**
5. **Visit exercise page**: https://exrx.net/WeightExercises/PectoralSternal/BWPushup
6. **You'll see the video file appear** (usually .mp4)
7. **Right-click the video file** → Copy URL
8. **Paste URL in new tab** → Right-click video → Save As

## Method 2: Using Video DownloadHelper Extension

1. Install "Video DownloadHelper" extension for Chrome/Firefox
2. Visit exercise page
3. Click extension icon
4. Download the video

## Method 3: Inspect Element

1. **Right-click on the video** → Inspect
2. **Look for `<video>` tag** in HTML
3. **Find `src=` attribute** with video URL
4. **Copy that URL** and download

## Where to Save Videos

Create this folder structure:
```
NutriCoachAI/
  assets/
    videos/
      exercises/
        chest/
          pushups.mp4
          bench-press.mp4
          dumbbell-flyes.mp4
          ...
        back/
        legs/
        shoulders/
        arms/
        abs/
```

## Example Video URLs from ExRx.net

Once you find the pattern, it might be something like:
- `https://exrx.net/ExMedia/PectoralSternal/BWPushup.mp4`
- `https://exrx.net/ExMedia/PectoralSternal/BBBenchPress.mp4`

## After Downloading

Tell me and I'll update the code to use your local video files!

---

## Alternative: Use Free Exercise GIF APIs

### Wger Workout Manager (Free & Open Source)
- Website: https://wger.de
- Has exercise database with images
- Free API access
- No authentication needed

### Example API Call:
```
https://wger.de/api/v2/exercise/?language=2&limit=999
```

This returns exercise data with image URLs!

---

## Quick Test

Try this URL in your browser to see if you can access ExRx video directly:
```
https://exrx.net/ExMedia/PectoralSternal/BWPushup.mp4
```

If it works, we can use this pattern for all exercises!
