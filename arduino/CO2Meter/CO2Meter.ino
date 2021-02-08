#include <Wire.h>
#include "SparkFun_SCD30_Arduino_Library.h"
SCD30 airSensor;

#include <MKRWAN.h>

LoRaModem modem;

#include "arduino_secrets.h"
String appEui = SECRET_APP_EUI;
String appKey = SECRET_APP_KEY;

void setup() {
  pinMode(LED_BUILTIN, OUTPUT);
  Serial.begin(115200);
  while (!Serial);

  Serial.println("CO2 Meter");

  Wire.begin();

  //Check for the Sensor
  if (! airSensor.begin(Wire)) {
    Serial.println("Sensor not found :(");
    while (1);
  }
  Serial.println("Sensor found");

  //Settings
  airSensor.setMeasurementInterval(548); //Measure every 10 minutes
  airSensor.setAltitudeCompensation(450);

  if (!modem.begin(EU868)) {
    Serial.println("Failed to start module");
    while (1) {}
  };

  Serial.println("Connecting ...");
  int connected = modem.joinOTAA(appEui, appKey);
  //  while (!modem.joinOTAA(appEui, appKey));

  modem.minPollInterval(60);
}

void loop() {
  digitalWrite(LED_BUILTIN, LOW);
  delay(500);
  if (airSensor.dataAvailable())
  {
    Serial.println();
    Serial.print("Temperature: "); Serial.print(airSensor.getTemperature()); Serial.print(" °C\t");
    Serial.print("Humidity: "); Serial.print(airSensor.getHumidity()); Serial.print(" %\t");
    Serial.print("CO2: "); Serial.print(airSensor.getCO2()); Serial.println(" ppm");

    //  Save data in smallest possible data type
    int8_t roundedHumidity = round(airSensor.getHumidity());
    int16_t roundedTemperature = round(airSensor.getTemperature() * 10); // *10 for 1 decimal place
    int16_t co2 = airSensor.getCO2();

    //  Put data into payload
    byte payload[5];
    payload[0] = roundedHumidity; //int8_t has only one byte
    payload[1] = highByte(roundedTemperature);
    payload[2] = lowByte(roundedTemperature);
    payload[3] = highByte(co2);
    payload[4] = lowByte(co2);

    //  Send payload
    int err;
    modem.beginPacket();
    modem.write(payload, sizeof(payload));
    err = modem.endPacket(true);
    if (err > 0) {
      Serial.println("Message sent correctly!");
    } else {
      Serial.println("Error sending message :(");
    }
    delay(1000);
    //    if (!modem.available()) {
    //      Serial.println("No downlink message received at this time.");
    //      return;
    //    }
    //    char rcv[64];
    //    int i = 0;
    //    while (modem.available()) {
    //      rcv[i++] = (char)modem.read();
    //    }
    //    Serial.print("Received: ");
    //    for (unsigned int j = 0; j < i; j++) {
    //      Serial.print(rcv[j] >> 4, HEX);
    //      Serial.print(rcv[j] & 0xF, HEX);
    //      Serial.print(" ");
    //    }
    //    Serial.println();
  }
  else {
    digitalWrite(LED_BUILTIN, HIGH);
  }

  delay(500);
}
