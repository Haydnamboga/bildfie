import { IsString, IsNotEmpty, IsEnum, IsOptional, IsNumber, IsDateString, MinLength } from "class-validator";
import { TradeCategory } from "@prisma/client";

export class CreateJobPostDto {
  @IsString()
  @MinLength(3)
  title: string;

  @IsString()
  @MinLength(10)
  description: string;

  @IsEnum(TradeCategory)
  category: TradeCategory;

  @IsString()
  @IsNotEmpty()
  location: string;

  @IsOptional()
  @IsNumber()
  budgetMin?: number;

  @IsOptional()
  @IsNumber()
  budgetMax?: number;

  @IsOptional()
  @IsDateString()
  dueDate?: string;
}
