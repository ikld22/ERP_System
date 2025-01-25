import sys
import joblib
import json

# Attempt to load the model
try:
    with open('C:\\wamp64\\www\\ERP_System\\employee_model.pkl', 'rb') as file:
        model = joblib.load(file)
        print("Model loaded successfully.")
except Exception as e:
    print("Error loading model:", e)
    sys.exit()

# Read input data
try:
    data = json.loads(sys.argv[1])  # Get input data from JSON
    print("Data received:", data)  # Log received data
    
    if len(data) < 3:
        raise ValueError("Insufficient data provided. Expected at least 3 elements.")
except Exception as e:
    print("Error in receiving data:", e)
    sys.exit()

# Prepare features for prediction
features = [data[0], data[1], data[2]]  # Experience, Skills, Education
print("Features for prediction:", features)  # Log features

# Make prediction
try:
    predicted_label = model.predict([features])[0]
    print("Predicted label:", predicted_label)  # Log predicted label
except Exception as e:
    print("Error during prediction:", e)
    sys.exit()

print(predicted_label)  # Output the predicted label